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

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected MasterDepartment $department;
    protected MasterPosition $position;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $role = Role::firstOrCreate(['name' => 'Admin Maintenance']);
        
        // Seed default departments
        $this->department = MasterDepartment::firstOrCreate(
            ['code' => 'MAINTENANCE'],
            ['name' => 'Maintenance', 'sort_order' => 20]
        );

        // Seed positions
        $this->position = MasterPosition::firstOrCreate(
            ['code' => 'OPERATOR'],
            ['name' => 'Operator / Mekanik', 'sort_order' => 60]
        );

        // Create Admin User
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole($role);
        
        $this->actingAs($this->adminUser);
    }

    /**
     * Test creating a new employee.
     */
    public function test_employee_creation()
    {
        $response = $this->post(route('admin.employees.store'), [
            'employee_number' => '9199',
            'full_name' => 'Deny Romadhon',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => '1',
            'primary_skill' => 'Mechanical',
            'level' => 'Senior',
            'phone' => '0812345678',
            'remarks' => 'New recruit',
        ]);

        $response->assertRedirect(route('admin.index'));
        
        $this->assertDatabaseHas('employees', [
            'employee_number' => '9199',
            'employee_index' => 1,
            'employee_code' => '9199',
            'full_name' => 'Deny Romadhon',
            'employment_status' => 'ACTIVE',
            'is_assignable' => true,
        ]);
    }

    /**
     * Test editing an employee.
     */
    public function test_employee_editing()
    {
        $emp = Employee::create([
            'employee_number' => '1001',
            'employee_index' => 1,
            'employee_code' => '1001',
            'full_name' => 'Original Name',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => true,
        ]);

        $response = $this->put(route('admin.employees.update', $emp->id), [
            'employee_number' => '1001',
            'full_name' => 'Updated Name',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'RESIGNED',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => '0',
            'primary_skill' => 'Electrical',
            'level' => 'Senior Specialist',
        ]);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $emp->id,
            'full_name' => 'Updated Name',
            'employment_status' => 'RESIGNED',
            'is_assignable' => false,
            'primary_skill' => 'Electrical',
        ]);
    }

    /**
     * Test employee number reuse & auto code suffixing.
     */
    public function test_employee_number_reuse_suffixing()
    {
        // 1. Create first employee
        $emp1 = Employee::create([
            'employee_number' => '9199',
            'employee_index' => 1,
            'employee_code' => '9199',
            'full_name' => 'Deny Romadhon',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => true,
        ]);

        // 2. Try to create another employee with same number while first is ACTIVE (should fail validation)
        $response = $this->post(route('admin.employees.store'), [
            'employee_number' => '9199',
            'full_name' => 'Andi',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-07-29',
        ]);

        $response->assertSessionHasErrors('employee_number');

        // 3. Resign the first employee
        $emp1->update(['employment_status' => EmploymentStatus::RESIGNED]);

        // 4. Create new employee with same number (should succeed with index 2 / suffix '9199.2')
        $response2 = $this->post(route('admin.employees.store'), [
            'employee_number' => '9199',
            'full_name' => 'Andi',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-07-29',
        ]);

        $response2->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('employees', [
            'employee_number' => '9199',
            'employee_index' => 2,
            'employee_code' => '9199.2',
            'full_name' => 'Andi',
            'employment_status' => 'ACTIVE',
        ]);
    }

    /**
     * Test user login account linking/unlinking.
     */
    public function test_linked_user_login()
    {
        $emp = Employee::create([
            'employee_number' => '1002',
            'employee_index' => 1,
            'employee_code' => '1002',
            'full_name' => 'Worker Bob',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
        ]);

        $otherUser = User::factory()->create([
            'name' => 'Worker Bob Login',
            'email' => 'bob@test.com',
        ]);

        // Link User via update
        $this->put(route('admin.employees.update', $emp->id), [
            'employee_number' => '1002',
            'full_name' => 'Worker Bob',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'linked_user_id' => $otherUser->id,
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $emp->id,
            'linked_user_id' => $otherUser->id,
        ]);

        // Unlink User
        $this->put(route('admin.employees.update', $emp->id), [
            'employee_number' => '1002',
            'full_name' => 'Worker Bob',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'linked_user_id' => '',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $emp->id,
            'linked_user_id' => null,
        ]);
    }

    /**
     * Test dropdown lists only load ACTIVE and assignable employees.
     */
    public function test_technician_assignment_filters()
    {
        // Clear all initial seeded employees from migration for strict checking
        Employee::query()->delete();

        // 1. Active & Assignable
        Employee::create([
            'employee_number' => '1001',
            'employee_index' => 1,
            'employee_code' => '1001',
            'full_name' => 'Active Assignable Tech',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => true,
        ]);

        // 2. Active but Not Assignable
        Employee::create([
            'employee_number' => '1002',
            'employee_index' => 1,
            'employee_code' => '1002',
            'full_name' => 'Active Not Assignable',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => false,
        ]);

        // 3. Resigned but Assignable
        Employee::create([
            'employee_number' => '1003',
            'employee_index' => 1,
            'employee_code' => '1003',
            'full_name' => 'Resigned Assignable',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'RESIGNED',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => true,
        ]);

        $response = $this->get(route('breakdowns.index'));
        $response->assertStatus(200);
        
        $operators = $response->viewData('operators');
        
        $this->assertContains('Active Assignable Tech', $operators);
        $this->assertNotContains('Active Not Assignable', $operators);
        $this->assertNotContains('Resigned Assignable', $operators);
    }

    /**
     * Test completed maintenance log records are not altered by subsequent resignation.
     */
    public function test_historical_maintenance_integrity()
    {
        $emp = Employee::create([
            'employee_number' => '9199',
            'employee_index' => 1,
            'employee_code' => '9199',
            'full_name' => 'Deny Romadhon',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
            'is_assignable' => true,
        ]);

        $machine = Machine::create([
            'code' => 'MAC-HIST',
            'name' => 'Historical Machine',
            'department' => 'Machining',
            'category' => 'CNC',
            'production_area' => 'Machining Area',
            'operational_status' => 'running',
            'lifecycle_status' => 'ACTIVE',
            'manufacturer' => 'Siemens',
            'model' => 'H1',
            'serial_number' => 'SN-H1',
            'installation_date' => '2026-01-01',
            'commissioning_date' => '2026-01-01',
        ]);

        $plan = MaintenancePlan::create([
            'machine_id' => $machine->id,
            'type' => MaintenancePlanType::CORRECTIVE,
            'status' => 'completed',
            'breakdown_number' => 'BD-2026-001',
            'reported_at' => now(),
            'reported_by' => 'Operator',
            'reported_department' => 'Machining',
            'scheduled_date' => now(),
            'assigned_technician' => 'Deny Romadhon',
        ]);

        // Resign Employee
        $emp->update(['employment_status' => EmploymentStatus::RESIGNED]);

        // Query the maintenance plan and ensure the assigned technician name is unchanged
        $retrievedPlan = MaintenancePlan::find($plan->id);
        $this->assertEquals('Deny Romadhon', $retrievedPlan->assigned_technician);
    }

    /**
     * Test historical KPI eligibility rules.
     */
    public function test_historical_kpi_eligibility()
    {
        // 1. Create Active employee
        $empActive = Employee::create([
            'employee_number' => '1010',
            'employee_index' => 1,
            'employee_code' => '1010',
            'full_name' => 'Active Worker',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'ACTIVE',
            'employment_start_date' => '2026-01-01',
        ]);

        // 2. Create Resigned employee
        $empResigned = Employee::create([
            'employee_number' => '1020',
            'employee_index' => 1,
            'employee_code' => '1020',
            'full_name' => 'Resigned Worker',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'RESIGNED',
            'employment_start_date' => '2025-01-01',
            'employment_end_date' => '2026-06-30',
        ]);

        // Assert current active KPI dashboard eligibility: only ACTIVE employees
        $activeKpis = Employee::where('employment_status', EmploymentStatus::ACTIVE)->get();
        $this->assertTrue($activeKpis->contains($empActive));
        $this->assertFalse($activeKpis->contains($empResigned));

        // Assert historical KPI eligibility: resigned employee is visible for historical dates
        $targetYear = 2025;
        $eligibleFor2025 = Employee::whereYear('employment_start_date', '<=', $targetYear)
            ->where(function($q) use ($targetYear) {
                $q->whereNull('employment_end_date')
                  ->orWhereYear('employment_end_date', '>=', $targetYear);
            })->get();

        $this->assertTrue($eligibleFor2025->contains($empResigned));
    }
}
