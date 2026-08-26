<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\ProcurementCategory;
use App\Enums\ProcurementStatus;
use App\Enums\ProcurementUrgency;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementSourcingTypeTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Machine $machine;
    protected ProcurementCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }

        $this->machine = Machine::first() ?? Machine::create([
            'code' => 'MCH-TEST-99',
            'name' => 'Machine Test 99',
            'department' => 'MAINTENANCE',
            'production_area' => 'MAINTENANCE',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
        ]);

        $this->category = ProcurementCategory::first() ?? ProcurementCategory::create([
            'name' => 'Electrical',
            'slug' => 'electrical',
            'is_active' => true,
        ]);
    }

    public function test_create_procurement_with_local_sourcing_type()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Local Component A',
            'quantity_required' => 1,
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test local description',
            'reason' => 'Test reason',
            'sourcing_type' => 'local',
        ]);

        $response->assertStatus(302);
        $case = ProcurementCase::where('item_name', 'Local Component A')->firstOrFail();
        $this->assertEquals('local', $case->sourcing_type);
    }

    public function test_create_procurement_with_import_sourcing_type()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Import Component B',
            'quantity_required' => 1,
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test import description',
            'reason' => 'Test reason',
            'sourcing_type' => 'import',
        ]);

        $response->assertStatus(302);
        $case = ProcurementCase::where('item_name', 'Import Component B')->firstOrFail();
        $this->assertEquals('import', $case->sourcing_type);
    }

    public function test_validation_rejects_invalid_sourcing_type()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Invalid Sourcing Component',
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test description',
            'reason' => 'Test reason',
            'sourcing_type' => 'interstellar', // invalid sourcing type
        ]);

        $response->assertSessionHasErrors(['sourcing_type']);
        $this->assertDatabaseMissing('procurement_cases', [
            'item_name' => 'Invalid Sourcing Component',
        ]);
    }

    public function test_existing_procurement_with_null_sourcing_type_renders_correctly()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ymd') . '-9999',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Existing Component',
            'urgency' => 'normal',
            'status' => 'draft',
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test description',
            'reason' => 'Test reason',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
            'sourcing_type' => null, // existing null sourcing type
        ]);

        $this->actingAs($this->adminUser);

        // Show page
        $responseShow = $this->get(route('procurements.show', $case->id));
        $responseShow->assertStatus(200);

        // Edit page
        $responseEdit = $this->get(route('procurements.edit', $case->id));
        $responseEdit->assertStatus(200);

        // List page
        $responseList = $this->get(route('procurements.index'));
        $responseList->assertStatus(200);

        // PDF Print page
        $responsePdf = $this->get(route('procurements.print', $case->id));
        $responsePdf->assertStatus(200);
    }
}
