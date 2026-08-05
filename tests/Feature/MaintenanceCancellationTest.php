<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceTemplate;
use App\Models\User;
use App\Enums\MaintenancePlanType;
use App\Models\MaintenanceExecution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Machine $machine;
    protected MaintenanceTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed default roles and database records
        $this->seed();

        // Retrieve seeded user or create a new one with full permissions/role
        $this->adminUser = User::whereHas('roles', function($q) {
            $q->where('name', 'System Administrator');
        })->first() ?? User::factory()->create()->assignRole('System Administrator');
        
        $this->actingAs($this->adminUser);

        $this->machine = Machine::first() ?? Machine::create([
            'code' => 'CNC-99',
            'name' => 'Milling Machine 99',
            'department' => 'Machining',
            'production_area' => 'Area A',
            'category' => 'Milling Machine',
            'criticality' => 'high',
            'operational_status' => 'running',
            'is_active' => true,
            'lifecycle_status' => 'ACTIVE',
        ]);

        $this->template = MaintenanceTemplate::first() ?? MaintenanceTemplate::create([
            'name' => 'Servis Bulanan CNC Milling',
            'estimated_duration' => 120,
            'maintenance_type' => 'Bulanan',
        ]);
    }

    /**
     * Test allowed cancellation for Draft PM Plan.
     */
    public function test_pm_plan_cancellation_allowed_for_draft_status(): void
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'draft',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        $response = $this->post(route('planning.cancel', $plan->id), [
            'cancellation_reason' => 'Mesin sedang dipindahkan ke area lain',
        ]);

        $response->assertRedirect(route('preventive.index'));
        $response->assertSessionHas('success');

        $plan->refresh();
        $this->assertEquals('cancelled', $plan->status);
        $this->assertEquals('Mesin sedang dipindahkan ke area lain', $plan->cancellation_reason);
        $this->assertEquals($this->adminUser->id, $plan->cancelled_by);
        $this->assertNotNull($plan->cancelled_at);
    }

    /**
     * Test blocked cancellation for executed PM Plan.
     */
    public function test_pm_plan_cancellation_blocked_after_execution_starts(): void
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'in_progress',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        // Create execution record simulating execution start
        MaintenanceExecution::create([
            'maintenance_plan_id' => $plan->id,
            'machine_id' => $this->machine->id,
            'operator_name' => 'Alex Jon Wilis',
            'overall_score' => 5.0,
            'started_at' => now(),
        ]);

        $response = $this->post(route('planning.cancel', $plan->id), [
            'cancellation_reason' => 'Batal',
        ]);

        $response->assertSessionHas('error');
        $plan->refresh();
        $this->assertNotEquals('cancelled', $plan->status);
    }

    /**
     * Test autocomplete endpoint returns active plans.
     */
    public function test_autocomplete_endpoint_returns_expected_results(): void
    {
        // Create an active plan
        $plan1 = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'draft',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        // Create a cancelled plan
        $plan2 = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'cancelled',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
        ]);

        $response = $this->get(route('planning.autocomplete-replacements', [
            'q' => 'Milling',
            'type' => 'pm',
        ]));

        $response->assertStatus(200);
        $data = $response->json();

        // Should return plan1 but NOT plan2
        $ids = collect($data)->pluck('id')->toArray();
        $this->assertContains($plan1->id, $ids);
        $this->assertNotContains($plan2->id, $ids);
    }

    /**
     * Test planning board index excludes cancelled plans.
     */
    public function test_planning_board_index_excludes_cancelled_plans(): void
    {
        $plan1 = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'draft',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
            'notes' => 'Rencana Aktif Pertama Unik',
        ]);

        $plan2 = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'cancelled',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
            'notes' => 'Rencana Batal Kedua Unik',
        ]);

        $response = $this->get(route('planning.index'));
        $response->assertStatus(200);
        
        // Assert plan1 is listed/visible, but plan2 is excluded
        $response->assertSee('Rencana Aktif Pertama Unik');
        $response->assertDontSee('Rencana Batal Kedua Unik');
    }

    /**
     * Test breakdown visibility on index page when cancelled.
     */
    public function test_cancelled_breakdown_remains_visible_on_breakdown_index(): void
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'cancelled',
            'type' => MaintenancePlanType::CORRECTIVE,
            'breakdown_number' => 'BD-999999',
            'reported_at' => now(),
            'reported_by' => 'J. Doe',
            'reported_department' => 'Machining',
            'notes' => 'Kerusakan Sensor Suhu',
            'generation_source' => 'Manual',
            'cancellation_reason' => 'Laporan salah input',
            'cancelled_at' => now(),
            'cancelled_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('breakdowns.index'));
        $response->assertStatus(200);
        
        // Should list cancelled breakdown
        $response->assertSee('BD-999999');
        $response->assertSee('cancelled');

        // Detail page should load and show cancellation info
        $responseDetail = $this->get(route('breakdowns.show', $plan->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('STATUS: CANCELLED');
        $responseDetail->assertSee('Laporan salah input');
    }

    /**
     * Test preventive visibility on index page when cancelled.
     */
    public function test_cancelled_pm_remains_visible_on_preventive_index(): void
    {
        $plan = MaintenancePlan::create([
            'machine_id' => $this->machine->id,
            'maintenance_template_id' => $this->template->id,
            'scheduled_date' => now(),
            'priority' => 'high',
            'status' => 'cancelled',
            'type' => MaintenancePlanType::PM,
            'generation_source' => 'Manual',
            'cancellation_reason' => 'Maintenance rescheduled to next month',
            'cancelled_at' => now(),
            'cancelled_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('preventive.index'));
        $response->assertStatus(200);
        
        // Should list cancelled PM plan
        $response->assertSee($plan->work_order_number);
        $response->assertSee('cancelled');

        // Detail page should load and show cancellation info
        $responseDetail = $this->get(route('preventive.show', $plan->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('STATUS: CANCELLED');
        $responseDetail->assertSee('Maintenance rescheduled to next month');
    }
}
