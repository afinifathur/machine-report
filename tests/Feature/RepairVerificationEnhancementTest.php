<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\MasterDepartment;
use App\Models\MasterPosition;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceExecution;
use App\Models\Machine;
use App\Enums\EmploymentStatus;
use App\Enums\MaintenancePlanType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class RepairVerificationEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Employee $adminEmployee;
    protected MasterDepartment $department;
    protected MasterPosition $position;
    protected Machine $machine;
    protected MaintenancePlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

        // Link Employee
        $this->adminEmployee = Employee::create([
            'employee_number' => '9999',
            'employee_index' => 1,
            'employee_code' => '9999',
            'full_name' => 'Admin User',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => EmploymentStatus::ACTIVE,
            'employment_start_date' => '2026-01-01',
            'is_assignable' => true,
            'linked_user_id' => $this->adminUser->id,
        ]);

        $this->machine = Machine::create([
            'code' => 'MAC-VERIFY-01',
            'name' => 'Verify Test Machine 01',
            'department' => 'Machining',
            'category' => 'CNC',
            'production_area' => 'Machining Area',
            'operational_status' => 'breakdown',
            'lifecycle_status' => 'ACTIVE',
            'manufacturer' => 'Siemens',
            'model' => 'V1',
            'serial_number' => 'SN-V1',
            'installation_date' => '2026-01-01',
            'commissioning_date' => '2026-01-01',
        ]);

        $this->plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'type' => MaintenancePlanType::CORRECTIVE,
            'status' => 'reported',
            'breakdown_number' => 'BD-2026-VER1',
            'reported_at' => now(),
            'reported_by' => 'Operator',
            'reported_department' => 'Machining',
            'scheduled_date' => now(),
        ]);

        $this->actingAs($this->adminUser);
    }

    /**
     * Test verification page load defaults.
     */
    public function test_verification_page_contains_defaults_and_read_only_summary()
    {
        $response = $this->get(route('planning.execute', $this->plan->id));
        $response->assertStatus(200);

        // Verify summary fields
        $response->assertSee('Ringkasan Riwayat Perbaikan (Read-only)');
        $response->assertSee($this->plan->breakdown_number);
        $response->assertSee('MAC-VERIFY-01');

        // Verify Verified By defaults to current logged-in employee name
        $response->assertSee('Admin User');
    }

    /**
     * Test submitting repair with multiple technicians, custom operational statuses, and spareparts.
     */
    public function test_submitting_enhanced_repair_completion_report()
    {
        $photo = UploadedFile::fake()->image('after.jpg');
        $photoBefore = UploadedFile::fake()->image('before.jpg');

        // Form post payload matches controller's expectations
        // The advanced fields are combined into notes via JS on the frontend
        $reportData = [
            'verified_by' => 'Admin User',
            'team' => ['Edwin Permadi', 'Diki Yoga Saputra'],
            'actual_status' => 'temporary_repair',
            'condition_score' => '3',
            'remaining_issues' => 'Hydraulic seal leaks slightly',
            'repair_type' => 'Temporary',
            'follow_up' => 'Replace seal on next PM',
            'user_notes' => 'Temporary patch applied.'
        ];

        $notesString = "[REPORT:" . json_encode($reportData) . "]\n--- SUMMARY ---";

        $response = $this->post(route('planning.store-execute', $this->plan->id), [
            'operator_name' => 'Admin User',
            'operational_status' => 'running', // maps to database enum
            'overall_score' => '3',
            'notes' => $notesString,
            'photo' => $photo,
            'photo_before' => $photoBefore,
            'spareparts' => [
                'PART-6204' => [
                    'checked' => '1',
                    'qty' => 2
                ],
                'PART-6205' => [
                    'checked' => '1',
                    'qty' => 5
                ]
            ]
        ]);

        $response->assertRedirect(route('planning.show', $this->plan->id));

        // Assert database records
        $this->assertDatabaseHas('maintenance_plans', [
            'id' => $this->plan->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('machines', [
            'id' => $this->machine->id,
            'operational_status' => 'running',
        ]);

        $this->assertDatabaseHas('maintenance_execution_spareparts', [
            'warehouse_item_code' => 'PART-6204',
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('maintenance_execution_spareparts', [
            'warehouse_item_code' => 'PART-6205',
            'quantity' => 5,
        ]);

        // Assert details page parses and renders the enhanced JSON properties
        $detailResponse = $this->get(route('planning.show', $this->plan->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Laporan Penyelesaian Perbaikan');
        $detailResponse->assertSee('Verified By (Verifikator)');
        $detailResponse->assertSee('Admin User');
        $detailResponse->assertSee('Edwin Permadi, Diki Yoga Saputra');
        $detailResponse->assertSee('Sementara (Temporary)');
        $detailResponse->assertSee('Hydraulic seal leaks slightly');
        $detailResponse->assertSee('Replace seal on next PM');
    }

    /**
     * Test backward compatibility rendering.
     */
    public function test_backward_compatibility_notes_rendering()
    {
        // Create execution with old simple notes
        $execution = MaintenanceExecution::create([
            'maintenance_plan_id' => $this->plan->id,
            'machine_id' => $this->machine->id,
            'operator_name' => 'Operator Old',
            'started_at' => now(),
            'completed_at' => now(),
            'overall_score' => 4,
            'notes' => 'Old legacy plain text notes here.',
            'status' => 'completed',
        ]);

        $this->plan->update(['status' => 'completed']);

        $response = $this->get(route('planning.show', $this->plan->id));
        $response->assertStatus(200);
        $response->assertSee('Catatan Tambahan &amp; Tindakan Korektif', false);
        $response->assertSee('Old legacy plain text notes here.');
    }
}
