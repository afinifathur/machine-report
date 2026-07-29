<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MasterDepartment;
use App\Models\MaintenancePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorrectiveMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Machine $machine;
    protected MasterDepartment $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed standard DB structure & metadata
        $this->seed();

        $this->adminUser = User::first() ?? User::factory()->create();
        $this->actingAs($this->adminUser);

        $this->department = MasterDepartment::first();
        $this->machine = Machine::create([
            'code' => 'CNC-88',
            'name' => 'Milling Machine 88',
            'department' => $this->department->name,
            'production_area' => 'Area A',
            'category' => 'Milling Machine',
            'criticality' => 'high',
            'operational_status' => 'running',
            'manufacturer' => 'Siemens',
            'model' => 'X-Test',
            'serial_number' => 'SN-TEST-88',
            'installation_date' => '2020-01-01',
            'commissioning_date' => '2020-01-05',
            'vendor' => 'Test Vendor',
            'qr_code_path' => 'images/qr-test.png',
        ]);
    }

    /**
     * SCENARIO 1: Report Breakdown.
     */
    public function test_scenario_1_report_breakdown()
    {
        $response = $this->post(route('planning.store-breakdown'), [
            'machine_id' => $this->machine->id,
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
            'breakdown_description' => 'Spindle motor burned out',
            'priority' => 'critical',
        ]);

        $response->assertRedirect(route('planning.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('maintenance_plans', [
            'machine_id' => $this->machine->id,
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE->value,
            'status' => 'reported',
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
            'notes' => 'Spindle motor burned out',
            'priority' => 'critical',
        ]);

        $this->machine->refresh();
        $this->assertEquals('breakdown', $this->machine->operational_status);
    }

    /**
     * SCENARIO 2: Assign Technician.
     */
    public function test_scenario_2_assign_technician()
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'reported',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260729-001',
            'reported_at' => now(),
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
            'notes' => 'Some issue',
        ]);

        $response = $this->post(route('planning.assign-technician', $plan->id), [
            'assigned_technician' => 'Budi Utomo',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $plan->refresh();
        $this->assertEquals('assigned', $plan->status);
        $this->assertEquals('Budi Utomo', $plan->assigned_technician);
    }

    /**
     * SCENARIO 3: QR Routing (Corrective).
     */
    public function test_scenario_3_qr_routing_redirects_to_corrective()
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'reported',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260729-001',
            'reported_at' => now(),
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
            'notes' => 'Some issue',
        ]);

        $response = $this->get(route('planning.qr-entry', $this->machine->code));

        $response->assertRedirect(route('planning.execute', $plan->id));
    }

    /**
     * SCENARIO 4: Preventive Compatibility.
     */
    public function test_scenario_4_qr_routing_falls_back_to_preventive()
    {
        $pmPlan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'scheduled',
            'type' => \App\Enums\MaintenancePlanType::PM,
        ]);

        $response = $this->get(route('planning.qr-entry', $this->machine->code));

        $response->assertRedirect(route('planning.execute', $pmPlan->id));
    }

    /**
     * SCENARIO 5: Corrective Verification.
     */
    public function test_scenario_5_corrective_verification()
    {
        Storage::fake('public');

        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'assigned',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260729-001',
            'reported_at' => now()->subHours(2), // 2 hours ago
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
            'notes' => 'Some issue',
            'assigned_technician' => 'Budi Utomo',
        ]);

        $photoFile = UploadedFile::fake()->image('after.jpg');
        $photoBeforeFile = UploadedFile::fake()->image('before.jpg');

        $response = $this->post(route('planning.store-execute', $plan->id), [
            'operator_name' => 'R. Miller',
            'photo' => $photoFile,
            'photo_before' => $photoBeforeFile,
            'operational_status' => 'running',
            'overall_score' => 4,
            'notes' => 'Repaired successfully',
        ]);

        $response->assertRedirect(route('planning.show', $plan->id));
        $response->assertSessionHas('success');

        $plan->refresh();
        $this->machine->refresh();

        $this->assertEquals('completed', $plan->status);
        $this->assertNotNull($plan->completed_at);
        $this->assertEquals(120, $plan->downtime_duration); // 2 hours = 120 minutes
        $this->assertEquals('running', $this->machine->operational_status);

        $this->assertDatabaseHas('maintenance_executions', [
            'maintenance_plan_id' => $plan->id,
            'machine_id' => $this->machine->id,
            'operator_name' => 'R. Miller',
            'status' => 'completed',
        ]);
    }

    /**
     * SCENARIO 6: Spareparts association.
     */
    public function test_scenario_6_spareparts_association()
    {
        Storage::fake('public');

        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'assigned',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260729-001',
            'reported_at' => now()->subHours(1),
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
            'notes' => 'Some issue',
            'assigned_technician' => 'Budi Utomo',
        ]);

        $photoFile = UploadedFile::fake()->image('after.jpg');

        $response = $this->post(route('planning.store-execute', $plan->id), [
            'operator_name' => 'R. Miller',
            'photo' => $photoFile,
            'operational_status' => 'idle',
            'overall_score' => 5,
            'notes' => 'Done',
            'spareparts' => [
                'PART-XYZ' => [
                    'checked' => '1',
                    'qty' => 3
                ],
                'PART-ABC' => [
                    'checked' => '0',
                    'qty' => 1
                ]
            ]
        ]);

        $response->assertRedirect(route('planning.show', $plan->id));

        $execution = \App\Models\MaintenanceExecution::where('maintenance_plan_id', $plan->id)->first();
        $this->assertNotNull($execution);

        $this->assertDatabaseHas('maintenance_execution_spareparts', [
            'execution_id' => $execution->id,
            'warehouse_item_code' => 'PART-XYZ',
            'quantity' => 3,
        ]);

        $this->assertDatabaseMissing('maintenance_execution_spareparts', [
            'execution_id' => $execution->id,
            'warehouse_item_code' => 'PART-ABC',
        ]);
    }

    /**
     * SCENARIO 7: Machine History relation.
     */
    public function test_scenario_7_machine_history()
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'completed',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260729-001',
            'reported_at' => now()->subHours(1),
            'completed_at' => now(),
            'downtime_duration' => 60,
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
        ]);

        $correctiveHistory = $this->machine->correctiveHistory()->get();
        $this->assertCount(1, $correctiveHistory);
        $this->assertEquals($plan->id, $correctiveHistory->first()->id);
    }

    /**
     * SCENARIO 8: History Summary Service.
     */
    public function test_scenario_8_history_summary_service()
    {
        $service = new \App\Services\Maintenance\MachineHistorySummaryService();

        // 1. Empty State
        $summaryEmpty = $service->getSummary($this->machine);
        $this->assertEquals('-', $summaryEmpty['last_breakdown']);
        $this->assertEquals(0, $summaryEmpty['corrective_count']);
        $this->assertEquals('-', $summaryEmpty['average_mttr']);

        // 2. Single Record
        $plan1 = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now()->subDays(2),
            'priority' => 'high',
            'status' => 'completed',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260727-001',
            'reported_at' => now()->subDays(2)->subMinutes(90),
            'completed_at' => now()->subDays(2),
            'downtime_duration' => 90,
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
        ]);

        $summarySingle = $service->getSummary($this->machine);
        $this->assertEquals($plan1->completed_at->format('d/m/y H:i'), $summarySingle['last_breakdown']);
        $this->assertEquals(1, $summarySingle['corrective_count']);
        $this->assertEquals('90 Menit', $summarySingle['average_mttr']);

        // 3. Multiple Records
        MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now()->subDays(1),
            'priority' => 'high',
            'status' => 'completed',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260728-001',
            'reported_at' => now()->subDays(1)->subMinutes(30),
            'completed_at' => now()->subDays(1),
            'downtime_duration' => 30,
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
        ]);

        $summaryMultiple = $service->getSummary($this->machine);
        $this->assertEquals(2, $summaryMultiple['corrective_count']);
        $this->assertEquals('60 Menit', $summaryMultiple['average_mttr']); // (90 + 30) / 2 = 60
    }

    /**
     * SCENARIO 9: Downtime Calculation Service.
     */
    public function test_scenario_9_downtime_calculation()
    {
        $service = new \App\Services\Maintenance\DowntimeCalculationService();

        // Same day
        $start = now();
        $end = $start->copy()->addMinutes(45);
        $this->assertEquals(45, $service->calculateMinutes($start, $end));

        // Cross midnight
        $startMidnight = \Carbon\Carbon::parse('2026-07-28 23:30:00');
        $endMidnight = \Carbon\Carbon::parse('2026-07-29 00:45:00');
        $this->assertEquals(75, $service->calculateMinutes($startMidnight, $endMidnight));

        // Multi day
        $startMulti = \Carbon\Carbon::parse('2026-07-25 10:00:00');
        $endMulti = \Carbon\Carbon::parse('2026-07-27 12:00:00');
        // 2 days and 2 hours = 48 + 2 = 50 hours = 3000 minutes
        $this->assertEquals(3000, $service->calculateMinutes($startMulti, $endMulti));

        // Invalid completion time (completion before reported) - Expect Exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Completion time cannot be earlier than reported time.');
        $service->calculateMinutes(now(), now()->subMinutes(10));
    }

    /**
     * SCENARIO 10: Concurrency Breakdown Suffix.
     */
    public function test_scenario_10_concurrency_breakdown_number()
    {
        $service = new \App\Services\Maintenance\BreakdownNumberService();

        $num1 = $service->generateNextNumber();
        $this->assertStringEndsWith('-001', $num1);

        // Store a plan to increment database record
        MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'reported',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => $num1,
            'reported_at' => now(),
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
        ]);

        $num2 = $service->generateNextNumber();
        $this->assertNotEquals($num1, $num2);
        $this->assertStringEndsWith('-002', $num2);
    }

    /**
     * SCENARIO 11: Transaction Rollback on image compression failure.
     */
    public function test_scenario_11_rollback_on_image_failure()
    {
        // Start machine state as breakdown
        $this->machine->update(['operational_status' => 'breakdown']);

        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'assigned',
            'type' => \App\Enums\MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-20260729-001',
            'reported_at' => now()->subHours(1),
            'reported_by' => 'Operator John',
            'reported_department' => $this->department->name,
            'notes' => 'Some issue',
            'assigned_technician' => 'Budi Utomo',
        ]);

        // Mock the ImageCompressionService to throw an exception
        $this->mock(\App\Services\ImageCompressionService::class, function ($mock) {
            $mock->shouldReceive('compressAndStore')
                ->andThrow(new \Exception('Disk full or compression failed'));
        });

        $photoFile = UploadedFile::fake()->image('after.jpg');

        $response = $this->post(route('planning.store-execute', $plan->id), [
            'operator_name' => 'R. Miller',
            'photo' => $photoFile,
            'operational_status' => 'running',
            'overall_score' => 4,
            'notes' => 'Will be rolled back',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $plan->refresh();
        $this->machine->refresh();

        $this->assertEquals('assigned', $plan->status);
        $this->assertEquals('breakdown', $this->machine->operational_status); // Remains breakdown, not changed to running

        $this->assertDatabaseMissing('maintenance_executions', [
            'maintenance_plan_id' => $plan->id,
        ]);
    }
}
