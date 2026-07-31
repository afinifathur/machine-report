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

    /**
     * Verify PDF generation endpoint is resilient to unknown and unexpected sparepart statuses.
     */
    public function test_work_order_pdf_generation_with_unknown_and_unexpected_sparepart_statuses(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Mock the MachineSparepartService to return spareparts with unexpected DTO and status shapes
        $this->mock(\App\Integrations\WMS\Services\MachineSparepartService::class, function ($mock) {
            $mock->shouldReceive('getMachineSparepartsView')
                ->andReturn([
                    [
                        'qty_per_machine' => 2,
                        'dto' => new \App\Integrations\WMS\DTOs\SparepartItemDTO(
                            erpCode: 'PART-A',
                            variantId: 10,
                            name: 'Part A',
                            brand: 'Brand',
                            unit: 'pcs',
                            barcode: '111',
                            location: 'Location',
                            supplier: 'Supplier',
                            stock: 10,
                            weeklyAverage: 1.0,
                            category: 'Category',
                            isAvailable: true,
                            isOffline: false,
                            mappingId: 1
                        ),
                        'status' => 'StringStatus' // Unexpected string type instead of array
                    ],
                    [
                        'qty_per_machine' => 1,
                        'dto' => null, // Unexpected null DTO
                        'status' => null // Unexpected null status
                    ],
                    [
                        'qty_per_machine' => 4,
                        'dto' => new \App\Integrations\WMS\DTOs\SparepartItemDTO(
                            erpCode: 'PART-B',
                            variantId: 20,
                            name: 'Part B',
                            brand: 'Brand',
                            unit: 'pcs',
                            barcode: '222',
                            location: 'Location',
                            supplier: 'Supplier',
                            stock: 10,
                            weeklyAverage: 1.0,
                            category: 'Category',
                            isAvailable: true,
                            isOffline: false,
                            mappingId: 2
                        ),
                        'status' => [
                            'code' => 'custom_weird_status',
                            'label' => 'Custom'
                        ]
                    ]
                ]);
        });

        $response = $this->get(route('planning.print', $this->planPm->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    /**
     * Verify Completion Report PDF generation endpoint response.
     */
    public function test_completion_report_pdf_generation_endpoint(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Make sure execution exists to represent a completion report
        $execution = \App\Models\MaintenanceExecution::create([
            'maintenance_plan_id' => $this->planPm->id,
            'machine_id' => $this->machine->id,
            'operator_name' => 'Operator A',
            'started_at' => now()->subHour(),
            'completed_at' => now(),
            'overall_score' => 4.5,
            'notes' => 'Corrective actions done.',
            'status' => 'completed',
        ]);

        $this->planPm->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_completion' => now(),
        ]);

        $response = $this->get(route('planning.report', $this->planPm->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    /**
     * Verify Completion Report PDF resilience scenarios.
     */
    public function test_completion_report_pdf_resilience_scenarios(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Scenario: Multiple spareparts consumed, before & after photos present, delayed completion, unknown status
        $execution = \App\Models\MaintenanceExecution::create([
            'maintenance_plan_id' => $this->planCm->id,
            'machine_id' => $this->machine->id,
            'operator_name' => 'Operator B',
            'started_at' => now()->subHours(3),
            'completed_at' => now(),
            'overall_score' => 3.8,
            'notes' => 'Replaced spindle belts.',
            'status' => 'completed',
        ]);

        // Add consumed spareparts
        \App\Models\MaintenanceExecutionSparepart::create([
            'execution_id' => $execution->id,
            'warehouse_item_code' => 'SP-001',
            'quantity' => 2,
        ]);
        \App\Models\MaintenanceExecutionSparepart::create([
            'execution_id' => $execution->id,
            'warehouse_item_code' => 'SP-002',
            'quantity' => 1,
        ]);

        // Add mock photo records
        \App\Models\MaintenanceExecutionPhoto::create([
            'execution_id' => $execution->id,
            'type' => 'before',
            'photo_path' => 'photos/before_test.jpg',
        ]);
        \App\Models\MaintenanceExecutionPhoto::create([
            'execution_id' => $execution->id,
            'type' => 'after',
            'photo_path' => 'photos/after_test.jpg',
        ]);

        // Set delayed plan parameters
        $this->planCm->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_completion' => now(),
            'target_completion' => now()->subHours(2), // 2 hours late
            'delay_reason' => 'waiting_sparepart',
            'delay_notes' => 'WMS delivery delay',
        ]);

        // Mock WMS Repository lookup
        $this->mock(\App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('getItemsDetails')
                ->andReturn([
                    'SP-001' => new \App\Integrations\WMS\DTOs\SparepartItemDTO(
                        erpCode: 'SP-001',
                        variantId: 101,
                        name: 'Spindle Belt Type A',
                        brand: 'Gates',
                        unit: 'pcs',
                        barcode: 'Gates-001',
                        location: 'A-2',
                        supplier: 'Gates Indo',
                        stock: 5,
                        weeklyAverage: 0.5,
                        category: 'Power Transmission',
                        isAvailable: true,
                        isOffline: false,
                        mappingId: 101
                    ),
                    // SP-002 will be missing/null in the returned map to verify robustness
                ]);
        });

        $response = $this->get(route('planning.report', $this->planCm->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }
}
