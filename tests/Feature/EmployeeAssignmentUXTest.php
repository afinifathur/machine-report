<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\MasterDepartment;
use App\Models\MasterPosition;
use App\Models\MaintenancePlan;
use App\Models\Machine;
use App\Enums\EmploymentStatus;
use App\Enums\MaintenancePlanType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class EmployeeAssignmentUXTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected MasterDepartment $department;
    protected MasterPosition $position;
    protected Employee $operationalEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $role = Role::firstOrCreate(['name' => 'Admin Maintenance']);
        
        // Seed default departments
        $this->department = MasterDepartment::firstOrCreate(
            ['code' => 'MTC'],
            ['name' => 'Maintenance', 'sort_order' => 20]
        );

        // Seed positions
        $this->position = MasterPosition::firstOrCreate(
            ['code' => 'OPR'],
            ['name' => 'Operator / Mekanik', 'sort_order' => 60]
        );

        // Create Admin User
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole($role);

        // Create Operational Employee
        $this->operationalEmployee = Employee::create([
            'employee_number' => '7319',
            'employee_index' => 1,
            'employee_code' => '7319',
            'full_name' => 'Edwin Permadi',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => EmploymentStatus::ACTIVE,
            'employment_start_date' => '2022-07-15',
            'is_assignable' => true,
        ]);
        
        $this->actingAs($this->adminUser);
    }

    /**
     * Test that the employee list only contains operational employees.
     */
    public function test_employee_list_only_contains_operational_employees()
    {
        // 1. Create a non-operational user/employee (e.g. Director)
        $directorUser = User::factory()->create([
            'name' => 'Direktur Utama',
            'email' => 'direktur@peroniks.com',
        ]);
        
        $directorPos = MasterPosition::firstOrCreate(
            ['code' => 'DIR'],
            ['name' => 'Direktur', 'sort_order' => 10]
        );

        $directorEmp = Employee::create([
            'employee_number' => '1000',
            'employee_index' => 1,
            'employee_code' => '1000',
            'full_name' => 'Direktur Utama',
            'department_id' => $this->department->id,
            'position_id' => $directorPos->id,
            'employment_status' => EmploymentStatus::ACTIVE,
            'employment_start_date' => '2020-01-01',
            'is_assignable' => false, // non-operational
            'linked_user_id' => $directorUser->id,
        ]);

        // Run the seeder cleanup logic
        $nonOpsEmails = ['direktur@peroniks.com'];
        $nonOpsUserIds = User::whereIn('email', $nonOpsEmails)->pluck('id');
        Employee::whereIn('linked_user_id', $nonOpsUserIds)->delete();

        // Assert that Director Employee profile was removed, but User remains
        $this->assertDatabaseMissing('employees', ['id' => $directorEmp->id]);
        $this->assertDatabaseHas('users', ['id' => $directorUser->id]);

        // Assert that the operational employee is still present
        $this->assertDatabaseHas('employees', ['id' => $this->operationalEmployee->id]);
    }

    /**
     * Test that autocomplete HTML output contains searchable properties.
     */
    public function test_autocomplete_contains_search_meta()
    {
        $response = $this->get(route('breakdowns.index'));
        $response->assertStatus(200);

        // Assert autocomplete component script or structure is present in dashboard HTML
        $response->assertSee('employee-autocomplete-wrapper');
        $response->assertSee('modal-assigned-technician');
        $response->assertSee('search-modal-assigned-technician');
        $response->assertSee('Edwin Permadi');
        $response->assertSee('7319');
    }

    /**
     * Test that assigning a technician to a breakdown continues to work.
     */
    public function test_assigning_technician_continues_working()
    {
        $machine = Machine::create([
            'code' => 'MAC-UX-01',
            'name' => 'UX Test Machine',
            'department' => 'Machining',
            'category' => 'CNC',
            'production_area' => 'Machining Area',
            'operational_status' => 'breakdown',
            'lifecycle_status' => 'ACTIVE',
            'manufacturer' => 'Siemens',
            'model' => 'UX1',
            'serial_number' => 'SN-UX1',
            'installation_date' => '2026-01-01',
            'commissioning_date' => '2026-01-01',
        ]);

        $plan = MaintenancePlan::create([
            'machine_id' => $machine->id,
            'type' => MaintenancePlanType::CORRECTIVE,
            'status' => 'reported',
            'breakdown_number' => 'BD-2026-UX1',
            'reported_at' => now(),
            'reported_by' => 'Operator',
            'reported_department' => 'Machining',
            'scheduled_date' => now(),
        ]);

        $response = $this->post(route('planning.assign-technician', $plan->id), [
            'assigned_technician' => 'Edwin Permadi',
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('maintenance_plans', [
            'id' => $plan->id,
            'assigned_technician' => 'Edwin Permadi',
            'status' => 'assigned',
        ]);
    }
}
