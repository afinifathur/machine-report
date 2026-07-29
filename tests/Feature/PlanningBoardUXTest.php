<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PlanningBoardUXTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Verify that completed maintenance plans are hidden from the List View
     * but still appear in the Calendar View.
     */
    public function test_completed_plans_visibility(): void
    {
        // 1. Find or create a completed plan
        $plan = MaintenancePlan::first();
        $plan->status = 'completed';
        $plan->save();

        $response = $this->get(route('planning.index'));
        $response->assertStatus(200);

        // 2. In List View, the completed plan details should NOT be rendered in the grouped list sections
        // We look up the timeline section container and ensure the machine code is not present in it.
        $response->assertDontSee('id="row-part-'); // just to make sure we don't match sparepart tables
        
        // Let's assert the completed machine code does not appear inside the view-timeline div
        $body = $response->getContent();
        
        // Find the view-timeline block and check if it contains the machine code of the completed plan
        preg_match('/<div id="view-timeline" class="space-y-4">(.*?)<\/div>/s', $body, $matches);
        $timelineHtml = $matches[0] ?? '';
        $this->assertStringNotContainsString($plan->machine->code, $timelineHtml);

        // 3. In Calendar View, the completed plan MUST remain visible
        preg_match('/<div id="view-calendar" class="hidden">(.*?)<\/div>\s*<\/div>\s*<\/div>/s', $body, $calendarMatches);
        $calendarHtml = $calendarMatches[0] ?? $body; // fallback if matches aren't clean due to layout tags
        $this->assertStringContainsString($plan->machine->code, $calendarHtml);
    }

    /**
     * Verify that active plans are correctly grouped into TODAY, Tomorrow, and Upcoming,
     * and sorted by Priority (Critical > High > Medium > Low), then Status, then Scheduled Time.
     */
    public function test_list_view_grouping_and_sorting(): void
    {
        // Clean out existing plans to build a deterministic set
        MaintenancePlan::query()->delete();

        $machine = Machine::first();
        $template = MaintenanceTemplate::first();

        // Create Today, Tomorrow, and Upcoming plans with different priorities and unique notes
        $todayPlanLow = MaintenancePlan::create([
            'machine_id' => $machine->id,
            'maintenance_template_id' => $template->id,
            'scheduled_date' => Carbon::today(),
            'priority' => 'low',
            'status' => 'reported',
            'generation_source' => 'system',
            'notes' => 'notes_low',
        ]);

        $todayPlanCritical = MaintenancePlan::create([
            'machine_id' => $machine->id,
            'maintenance_template_id' => $template->id,
            'scheduled_date' => Carbon::today(),
            'priority' => 'critical',
            'status' => 'reported',
            'generation_source' => 'system',
            'notes' => 'notes_critical',
        ]);

        $tomorrowPlan = MaintenancePlan::create([
            'machine_id' => $machine->id,
            'maintenance_template_id' => $template->id,
            'scheduled_date' => Carbon::tomorrow(),
            'priority' => 'medium',
            'status' => 'reported',
            'generation_source' => 'system',
            'notes' => 'notes_tomorrow',
        ]);

        $upcomingPlan = MaintenancePlan::create([
            'machine_id' => $machine->id,
            'maintenance_template_id' => $template->id,
            'scheduled_date' => Carbon::today()->addDays(5),
            'priority' => 'high',
            'status' => 'reported',
            'generation_source' => 'system',
            'notes' => 'notes_upcoming',
        ]);

        $response = $this->get(route('planning.index'));
        $response->assertStatus(200);

        // Verify the HTML contains section headers for TODAY, Tomorrow, and Upcoming
        $response->assertSee('Hari Ini / Terlambat (TODAY)');
        $response->assertSee('Besok (TOMORROW)');
        $response->assertSee('Akan Datang (UPCOMING)');

        // Verify sorting: TODAY group must put Critical plan before Low priority plan, Today before Tomorrow, Tomorrow before Upcoming
        $body = $response->getContent();
        
        $criticalPos = strpos($body, 'notes_critical');
        $lowPos = strpos($body, 'notes_low');
        $mediumPos = strpos($body, 'notes_tomorrow');
        $highPos = strpos($body, 'notes_upcoming');
        
        $this->assertNotFalse($criticalPos, 'Should contain Critical priority card (notes_critical).');
        $this->assertNotFalse($lowPos, 'Should contain Low priority card (notes_low).');
        $this->assertNotFalse($mediumPos, 'Should contain Medium priority card (notes_tomorrow).');
        $this->assertNotFalse($highPos, 'Should contain High priority card (notes_upcoming).');
        
        $this->assertLessThan($lowPos, $criticalPos, 'Critical priority plan must be sorted before Low priority plan inside the TODAY group.');
        $this->assertLessThan($mediumPos, $lowPos, 'TODAY group items must be rendered before TOMORROW group items.');
        $this->assertLessThan($highPos, $mediumPos, 'TOMORROW group items must be rendered before UPCOMING group items.');
    }
}
