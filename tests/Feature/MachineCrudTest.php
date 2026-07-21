<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MasterDepartment;
use App\Models\MasterMachineCategory;
use App\Models\MasterProductionArea;
use App\Models\User;
use App\Models\MaintenancePlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected MasterDepartment $department;
    protected MasterMachineCategory $category;
    protected MasterProductionArea $productionArea;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed standard DB data first to ensure all master tables are populated
        $this->seed();

        // Retrieve seeded database records or create test user
        $this->adminUser = User::first() ?? User::factory()->create();
        
        // Authenticate the user
        $this->actingAs($this->adminUser);

        // Fetch master data
        $this->department = MasterDepartment::first();
        $this->category = MasterMachineCategory::first();
        $this->productionArea = MasterProductionArea::first();
    }

    /**
     * Test progressive registration works with just the required fields.
     */
    public function test_machine_progressive_registration_success(): void
    {
        $machineData = [
            'code' => 'CNC-99',
            'name' => 'Machine 99 CNC',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
        ];

        $response = $this->post(route('machines.store'), $machineData);

        $response->assertRedirect(route('machines.show', 'CNC-99'));
        
        $this->assertDatabaseHas('machines', [
            'code' => 'CNC-99',
            'name' => 'Machine 99 CNC',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'is_active' => true,
            'lifecycle_status' => 'ACTIVE',
            'created_by' => $this->adminUser->id,
        ]);
    }

    /**
     * Test validation rejects missing required fields.
     */
    public function test_machine_registration_fails_missing_fields(): void
    {
        $response = $this->post(route('machines.store'), [
            'code' => 'CNC-88',
            // Missing name, department, category, production_area_id
        ]);

        $response->assertSessionHasErrors(['name', 'department', 'category', 'production_area_id']);
        $this->assertDatabaseMissing('machines', ['code' => 'CNC-88']);
    }

    /**
     * Test machine code uniqueness validation.
     */
    public function test_machine_code_must_be_unique(): void
    {
        // First machine
        Machine::create([
            'code' => 'DUPLICATE-01',
            'name' => 'Machine A',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
        ]);

        // Attempting to register another machine with same code
        $response = $this->post(route('machines.store'), [
            'code' => 'DUPLICATE-01',
            'name' => 'Machine B',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * Test machine code is immutable and cannot be updated.
     */
    public function test_machine_code_is_immutable(): void
    {
        $machine = Machine::create([
            'code' => 'IMMUTABLE-01',
            'name' => 'Original Name',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'is_active' => true,
            'lifecycle_status' => 'ACTIVE',
        ]);

        $response = $this->put(route('machines.update', $machine->code), [
            'code' => 'MUTATED-01', // Attempting to change code
            'name' => 'Updated Name',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
        ]);

        $response->assertRedirect(route('machines.show', 'IMMUTABLE-01'));
        
        $this->assertDatabaseHas('machines', [
            'code' => 'IMMUTABLE-01',
            'name' => 'Updated Name',
            'production_area_id' => $this->productionArea->id,
        ]);

        $this->assertDatabaseMissing('machines', [
            'code' => 'MUTATED-01',
        ]);
    }

    /**
     * Test archive (soft deletion) updates status and is_active, but keeps the record.
     */
    public function test_machine_is_archived_instead_of_physically_deleted(): void
    {
        $machine = Machine::create([
            'code' => 'ARCHIVE-TEST',
            'name' => 'Archive Machine',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'is_active' => true,
            'lifecycle_status' => 'ACTIVE',
        ]);

        $response = $this->delete(route('machines.destroy', $machine->code));

        $response->assertRedirect(route('machines.index'));

        // The machine must still exist in the database, but as inactive and archived
        $this->assertDatabaseHas('machines', [
            'code' => 'ARCHIVE-TEST',
            'is_active' => false,
            'lifecycle_status' => 'INACTIVE',
        ]);

        $freshMachine = Machine::where('code', 'ARCHIVE-TEST')->first();
        $this->assertNotNull($freshMachine);
        $this->assertFalse($freshMachine->is_active);
    }

    /**
     * Test inactive/retired machines are excluded from default lists (e.g. index/dashboard/planning).
     */
    public function test_inactive_and_retired_machines_are_hidden_from_default_views(): void
    {
        // 1. Create an active machine
        $activeMachine = Machine::create([
            'code' => 'TEST-ACTIVE',
            'name' => 'Test Active Machine',
            'is_active' => true,
            'lifecycle_status' => 'ACTIVE',
            'department' => $this->department->name,
            'category' => $this->category->name,
        ]);

        // 2. Create an inactive machine
        $inactiveMachine = Machine::create([
            'code' => 'TEST-INACTIVE',
            'name' => 'Test Inactive Machine',
            'is_active' => false,
            'lifecycle_status' => 'INACTIVE',
            'department' => $this->department->name,
            'category' => $this->category->name,
        ]);

        // 3. Create a retired machine
        $retiredMachine = Machine::create([
            'code' => 'TEST-RETIRED',
            'name' => 'Test Retired Machine',
            'is_active' => false,
            'lifecycle_status' => 'RETIRED',
            'department' => $this->department->name,
            'category' => $this->category->name,
        ]);

        // Test Machines Index with default filter (should show only ACTIVE)
        $response = $this->get(route('machines.index'));
        $response->assertStatus(200);
        $response->assertSee('TEST-ACTIVE');
        $response->assertDontSee('TEST-INACTIVE');
        $response->assertDontSee('TEST-RETIRED');

        // Test Machines Index with status_filter=ALL (should show all machines)
        $responseAll = $this->get(route('machines.index', ['status_filter' => 'ALL']));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('TEST-ACTIVE');
        $responseAll->assertSee('TEST-INACTIVE');
        $responseAll->assertSee('TEST-RETIRED');

        // Test Dashboard (should not show priority data for inactive/retired)
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertDontSee('TEST-INACTIVE');
        $dashboardResponse->assertDontSee('TEST-RETIRED');
    }
}
