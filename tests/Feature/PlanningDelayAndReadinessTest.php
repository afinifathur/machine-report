<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceTemplate;
use App\Models\MaintenanceTemplateChecklist;
use App\Enums\MaintenancePlanType;
use App\Services\MaintenanceReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class PlanningDelayAndReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected Machine $machine;
    protected MaintenanceTemplate $template;
    protected MaintenancePlan $planPm;
    protected MaintenancePlan $planCm;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Create a machine
        $this->machine = Machine::create([
            'code' => 'CNC-99',
            'name' => 'Test Milling Machine',
            'department' => 'Machining Center',
            'production_area' => 'Area A',
            'category' => 'Milling Machine',
            'criticality' => 'high',
            'operational_status' => 'running',
            'manufacturer' => 'Siemens',
            'model' => 'X-Test',
            'serial_number' => 'SN-TEST-123',
            'installation_date' => '2020-01-01',
            'commissioning_date' => '2020-01-05',
            'vendor' => 'Test Vendor',
            'qr_code_path' => 'images/qr-test.png',
        ]);

        // Create a maintenance template
        $this->template = MaintenanceTemplate::create([
            'name' => 'Monthly CNC Test PM',
            'description' => 'Test PM Procedure',
            'machine_category' => 'Milling Machine',
            'maintenance_type' => 'Monthly',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        // Create checklists
        MaintenanceTemplateChecklist::create([
            'maintenance_template_id' => $this->template->id,
            'sequence' => 1,
            'title' => 'Check Spindle Oil Pressure',
            'is_required' => true,
        ]);

        // 1. Create PM Plan (checks default target_completion generation for PM)
        $this->planPm = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => Carbon::parse('2026-07-31'),
            'priority' => 'high',
            'status' => 'ready',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        // 2. Create CM Plan (checks default target_completion generation for corrective)
        $this->planCm = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => Carbon::parse('2026-07-31'),
            'priority' => 'critical',
            'status' => 'reported',
            'type' => MaintenancePlanType::CORRECTIVE,
            'generation_source' => 'Manual',
            'breakdown_number' => 'BD-999',
            'reported_at' => now(),
            'reported_by' => 'Operator A',
            'reported_department' => 'Production',
        ]);
    }

    /**
     * Verify target completion auto-generation logic.
     */
    public function test_target_completion_auto_generation(): void
    {
        // PM: scheduled_date (at 08:00) + template estimated_duration (60 mins)
        $expectedPmTarget = Carbon::parse('2026-07-31')->setTime(8, 0, 0)->addMinutes(60);
        $this->assertEquals($expectedPmTarget->toDateTimeString(), $this->planPm->target_completion->toDateTimeString());

        // CM: scheduled_date (at 08:00) + fallback duration (120 mins)
        $expectedCmTarget = Carbon::parse('2026-07-31')->setTime(8, 0, 0)->addMinutes(120);
        $this->assertEquals($expectedCmTarget->toDateTimeString(), $this->planCm->target_completion->toDateTimeString());
    }

    /**
     * Verify validation enforces delay reason when submitting after target completion.
     */
    public function test_validation_enforces_delay_reason_when_overdue(): void
    {
        // Update target completion to a past time
        $this->planPm->update([
            'target_completion' => now()->subHour(),
        ]);

        // Attempt PM submission without delay reason
        $response = $this->post(route('planning.store-execute', $this->planPm->id), [
            'operator_name' => 'Budi Utomo',
            'started_at' => now()->subMinutes(15)->format('Y-m-d H:i:s'),
            'answers' => [
                $this->template->checklists->first()->id => [
                    'score' => 4,
                ],
            ],
            'photo' => UploadedFile::fake()->image('checklist_proof.jpg'),
        ]);

        $response->assertSessionHasErrors(['delay_reason']);
    }

    /**
     * Verify validation enforces delay notes when delay reason is 'other'.
     */
    public function test_validation_enforces_delay_notes_for_other(): void
    {
        $this->planPm->update([
            'target_completion' => now()->subHour(),
        ]);

        // Attempt PM submission with delay reason 'other' but no notes
        $response = $this->post(route('planning.store-execute', $this->planPm->id), [
            'operator_name' => 'Budi Utomo',
            'started_at' => now()->subMinutes(15)->format('Y-m-d H:i:s'),
            'answers' => [
                $this->template->checklists->first()->id => [
                    'score' => 4,
                ],
            ],
            'photo' => UploadedFile::fake()->image('checklist_proof.jpg'),
            'delay_reason' => 'other',
            'delay_notes' => '',
        ]);

        $response->assertSessionHasErrors(['delay_notes']);
    }

    /**
     * Verify successful delay reason submission and database state.
     */
    public function test_successful_delay_submission(): void
    {
        $this->planPm->update([
            'target_completion' => now()->subHour(),
        ]);

        $response = $this->post(route('planning.store-execute', $this->planPm->id), [
            'operator_name' => 'Budi Utomo',
            'started_at' => now()->subMinutes(15)->format('Y-m-d H:i:s'),
            'answers' => [
                $this->template->checklists->first()->id => [
                    'score' => 4,
                ],
            ],
            'photo' => UploadedFile::fake()->image('checklist_proof.jpg'),
            'delay_reason' => 'waiting_sparepart',
        ]);

        $response->assertRedirect();
        
        $this->planPm->refresh();
        $this->assertEquals('completed', $this->planPm->status);
        $this->assertEquals('waiting_sparepart', $this->planPm->delay_reason);
        $this->assertNotNull($this->planPm->actual_completion);
    }

    /**
     * Verify that MaintenanceReadinessService includes sparepart readiness audit sections.
     */
    public function test_readiness_service_includes_mapped_spareparts(): void
    {
        $service = app(MaintenanceReadinessService::class);
        $report = $service->getReadinessReport($this->planPm);

        $this->assertArrayHasKey('sparepart_readiness_ready', $report);
        $this->assertArrayHasKey('mapped_spareparts', $report);
        $this->assertTrue($report['sparepart_readiness_ready']); // defaults to true since no mapped parts exist
    }

    /**
     * Verify PDF generation endpoint response.
     */
    public function test_work_order_pdf_generation_endpoint(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('planning.print', $this->planPm->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }
}
