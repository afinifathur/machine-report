<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MasterDepartment;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceTemplate;
use App\Models\MaintenanceTemplateChecklist;
use App\Models\User;
use App\Enums\MaintenancePlanType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class PreventiveMaintenanceWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Machine $machine;
    protected MasterDepartment $department;
    protected MaintenanceTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Seed basic DB elements (roles, master data, etc.)
        $this->seed();

        $this->adminUser = User::first() ?? User::factory()->create();
        $this->actingAs($this->adminUser);

        $this->department = MasterDepartment::first() ?? MasterDepartment::create(['name' => 'Machining', 'sort_order' => 1]);
        
        $this->machine = Machine::create([
            'code' => 'CNC-99',
            'name' => 'Milling Machine 99',
            'department' => $this->department->name,
            'production_area' => 'Area A',
            'category' => 'Milling Machine',
            'criticality' => 'high',
            'operational_status' => 'running',
            'manufacturer' => 'Siemens',
            'model' => 'X-Test',
            'serial_number' => 'SN-TEST-99',
            'installation_date' => '2020-01-01',
            'commissioning_date' => '2020-01-05',
            'vendor' => 'Test Vendor',
            'qr_code_path' => 'images/qr-test.png',
        ]);

        $this->template = MaintenanceTemplate::create([
            'name' => 'Servis Bulanan CNC Milling',
            'description' => 'SOP bulanan CNC Milling',
            'machine_category' => 'Milling Machine',
            'maintenance_type' => 'Monthly',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);

        MaintenanceTemplateChecklist::create([
            'maintenance_template_id' => $this->template->id,
            'sequence' => 1,
            'title' => 'Cek spindle oil',
            'is_required' => true,
        ]);
    }

    /**
     * Test PM Workspace rendering.
     */
    public function test_pm_dashboard_rendering(): void
    {
        $response = $this->get(route('preventive.index'));
        $response->assertStatus(200);

        // Assert KPI titles are present
        $response->assertSee("Today's PM", false);
        $response->assertSee('Overdue PM');
        $response->assertSee('Due This Week');
        $response->assertSee('Completed Today');

        // Assert monitoring list title is present
        $response->assertSee('Monitoring List (Preventive)');
    }

    /**
     * Test PM Plan creation.
     */
    public function test_pm_creation(): void
    {
        $response = $this->get(route('planning.create', ['type' => 'preventive']));
        $response->assertStatus(200);
        $response->assertSee('CNC-99');
        $response->assertSee('Servis Bulanan CNC Milling');

        $responsePost = $this->post(route('planning.store'), [
            'type' => 'preventive',
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => '2026-08-05',
            'priority' => 'medium',
            'notes' => 'Tindakan rutin bulanan',
            'assigned_technician' => 'R. Miller',
        ]);

        $responsePost->assertRedirect(route('preventive.index'));
        $responsePost->assertSessionHas('success');

        $this->assertDatabaseHas('maintenance_plans', [
            'machine_id' => $this->machine->id,
            'type' => MaintenancePlanType::PM->value,
            'status' => 'assigned',
            'priority' => 'medium',
            'notes' => 'Tindakan rutin bulanan',
            'assigned_technician' => 'R. Miller',
        ]);
    }

    /**
     * Test assigning technician to PM plan.
     */
    public function test_pm_assignment(): void
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => Carbon::parse('2026-08-05'),
            'priority' => 'high',
            'status' => 'draft',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        $response = $this->post(route('planning.assign-technician', $plan->id), [
            'assigned_technician' => 'S. Chen',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $plan->refresh();
        $this->assertEquals('S. Chen', $plan->assigned_technician);
        $this->assertEquals('assigned', $plan->status);
    }

    /**
     * Test PM readiness audit details loading.
     */
    public function test_pm_readiness_audit(): void
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => Carbon::parse('2026-08-05'),
            'priority' => 'high',
            'status' => 'draft',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        $response = $this->get(route('planning.show', $plan->id));
        $response->assertStatus(200);
        $response->assertSee('Audit Kesiapan PM');
        $response->assertSee('CNC-99');
    }

    /**
     * Test printing Work Order PDF.
     */
    public function test_pm_work_order_pdf_generation(): void
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => Carbon::parse('2026-08-05'),
            'priority' => 'high',
            'status' => 'draft',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        $response = $this->get(route('planning.print', $plan->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test executing PM and submitting completion report.
     */
    public function test_pm_execution_and_report_generation(): void
    {
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-08-01 09:00:00'));

        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => Carbon::parse('2026-08-01'),
            'priority' => 'high',
            'status' => 'assigned',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
            'assigned_technician' => 'R. Miller',
        ]);

        // Get execution checklist screen
        $responseExec = $this->get(route('planning.execute', $plan->id));
        $responseExec->assertStatus(200);
        $responseExec->assertSee('PEMERIKSAAN PM');

        // Post execution submission
        $checklistId = $this->template->checklists->first()->id;
        $responsePost = $this->post(route('planning.store-execute', $plan->id), [
            'operator_name' => 'R. Miller',
            'started_at' => '2026-08-01 08:00:00',
            'photo' => UploadedFile::fake()->image('pm_proof.jpg'),
            'notes' => 'Semua checklist diuji dan OK.',
            'answers' => [
                $checklistId => [
                    'score' => 5,
                    'remarks' => 'Tekanan oli normal',
                ]
            ]
        ]);

        $responsePost->assertRedirect(route('planning.show', $plan->id));

        $plan->refresh();
        $this->assertEquals('completed', $plan->status);

        // Generate Completion Report PDF
        $responsePdf = $this->get(route('planning.report', $plan->id));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('Content-Type', 'application/pdf');

        \Carbon\Carbon::setTestNow();
    }

    /**
     * Test shared calendar displays both CM and PM with CM / PM tags.
     */
    public function test_shared_calendar_contains_cm_and_pm(): void
    {
        // Create CM plan
        MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => Carbon::parse('2026-08-01'),
            'priority' => 'critical',
            'status' => 'reported',
            'type' => MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-CM-999',
            'reported_at' => now(),
            'reported_by' => 'Operator A',
            'reported_department' => 'Machining',
        ]);

        // Create PM plan
        MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => Carbon::parse('2026-08-01'),
            'priority' => 'medium',
            'status' => 'draft',
            'type' => MaintenancePlanType::PM,
        ]);

        $response = $this->get(route('planning.index', ['month' => 8, 'year' => 2026]));
        $response->assertStatus(200);
        $response->assertSee('[CM]');
        $response->assertSee('[PM]');
    }
}
