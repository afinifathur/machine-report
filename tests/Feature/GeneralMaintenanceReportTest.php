<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MasterDepartment;
use App\Models\MaintenancePlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GeneralMaintenanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $authorizedUser;
    protected User $unauthorizedUser;
    protected MasterDepartment $deptMachining;
    protected MasterDepartment $deptProduction;
    protected Machine $machineMachining;
    protected Machine $machineProduction;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed();

        $this->deptMachining = MasterDepartment::where('name', 'Machining')->first() 
            ?? MasterDepartment::create(['code' => 'MCH', 'name' => 'Machining', 'is_active' => true, 'sort_order' => 1]);
        
        $this->deptProduction = MasterDepartment::where('name', 'Production')->first() 
            ?? MasterDepartment::create(['code' => 'PRD', 'name' => 'Production', 'is_active' => true, 'sort_order' => 2]);

        $this->machineMachining = Machine::create([
            'code' => 'L-BN.04',
            'name' => 'BOR CNC 04',
            'department' => $this->deptMachining->name,
            'production_area' => 'Area 1',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
            'manufacturer' => 'Makino',
            'model' => 'BN-04',
            'serial_number' => 'SN-BN04',
            'installation_date' => '2020-01-01',
            'commissioning_date' => '2020-01-05',
            'vendor' => 'Makino Corp',
        ]);

        $this->machineProduction = Machine::create([
            'code' => 'PRD-01',
            'name' => 'Conveyor Line 01',
            'department' => $this->deptProduction->name,
            'production_area' => 'Area 2',
            'category' => 'Conveyor',
            'criticality' => 'medium',
            'operational_status' => 'running',
            'manufacturer' => 'Siemens',
            'model' => 'CV-01',
            'serial_number' => 'SN-CV01',
            'installation_date' => '2021-01-01',
            'commissioning_date' => '2021-01-05',
            'vendor' => 'Siemens Corp',
        ]);

        // Authorized user with report.view permission
        $this->authorizedUser = User::first() ?? User::factory()->create();
        if (!$this->authorizedUser->can('report.view')) {
            $permission = Permission::firstOrCreate(['name' => 'report.view']);
            $this->authorizedUser->givePermissionTo($permission);
        }

        // Unauthorized user without report.view
        $this->unauthorizedUser = User::factory()->create();
        $this->unauthorizedUser->syncPermissions([]);
        $this->unauthorizedUser->syncRoles([]);
    }

    /**
     * TEST 1: Date filtering.
     * Given maintenance records inside and outside the selected date range.
     * Filter 01 Aug 2026 -> 31 Aug 2026.
     * Assert only records in the period appear.
     */
    public function test_date_filtering_returns_only_records_in_selected_period()
    {
        // In-range record (Aug 2026)
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Spindle Tidak Bisa Mutar T-Belt Putus',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        // Out-of-range record (Jul 2026)
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Old July issue',
            'reported_at' => Carbon::parse('2026-07-15 08:00:00'),
            'actual_completion' => Carbon::parse('2026-07-16 09:00:00'),
            'scheduled_date' => '2026-07-15',
        ]);

        // Out-of-range record (Sep 2026)
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Future September issue',
            'reported_at' => Carbon::parse('2026-09-05 08:00:00'),
            'actual_completion' => Carbon::parse('2026-09-06 10:00:00'),
            'scheduled_date' => '2026-09-05',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'all',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Spindle Tidak Bisa Mutar T-Belt Putus');
        $response->assertDontSee('Old July issue');
        $response->assertDontSee('Future September issue');
    }

    /**
     * TEST 2: End date inclusive.
     * Record completed on 31 Aug 2026 23:59:59 IS included.
     * Record completed on 01 Sep 2026 00:00:00 is NOT included.
     */
    public function test_end_date_filter_is_inclusive()
    {
        // End date edge record (23:59:59)
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Late Night Repair on End Date',
            'reported_at' => Carbon::parse('2026-08-31 20:00:00'),
            'actual_completion' => Carbon::parse('2026-08-31 23:59:59'),
            'scheduled_date' => '2026-08-31',
        ]);

        // Excluded next day record (00:00:00)
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Next Day Midnight Repair',
            'reported_at' => Carbon::parse('2026-08-31 22:00:00'),
            'actual_completion' => Carbon::parse('2026-09-01 00:00:00'),
            'scheduled_date' => '2026-08-31',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'all',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Late Night Repair on End Date');
        $response->assertDontSee('Next Day Midnight Repair');
    }

    /**
     * TEST 3: Department filtering.
     * Filter by specific department returns only matching department records.
     */
    public function test_department_filter_returns_only_matching_department()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Machining Problem XYZ',
            'reported_at' => Carbon::parse('2026-08-10 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-10 11:00:00'),
            'scheduled_date' => '2026-08-10',
        ]);

        MaintenancePlan::create([
            'machine_id' => $this->machineProduction->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Production Problem ABC',
            'reported_at' => Carbon::parse('2026-08-10 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-10 12:00:00'),
            'scheduled_date' => '2026-08-10',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'Machining',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Machining Problem XYZ');
        $response->assertDontSee('Production Problem ABC');
    }

    /**
     * TEST 4: All departments filter.
     */
    public function test_all_departments_filter_returns_all_records()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Machining Problem XYZ',
            'reported_at' => Carbon::parse('2026-08-10 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-10 11:00:00'),
            'scheduled_date' => '2026-08-10',
        ]);

        MaintenancePlan::create([
            'machine_id' => $this->machineProduction->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Production Problem ABC',
            'reported_at' => Carbon::parse('2026-08-10 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-10 12:00:00'),
            'scheduled_date' => '2026-08-10',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'all',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Machining Problem XYZ');
        $response->assertSee('Production Problem ABC');
        $response->assertSee('2 Maintenance');
    }

    /**
     * TEST 5: Correct machine information (code and name).
     */
    public function test_report_displays_machine_code_and_name()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Bearing noise on spindle',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'all',
            ]));

        $response->assertStatus(200);
        $response->assertSee('L-BN.04');
        $response->assertSee('BOR CNC 04');
    }

    /**
     * TEST 6: Canonical problem description.
     */
    public function test_report_uses_canonical_problem_description()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Spindle Tidak Bisa Mutar T-Belt Putus, Boring Saluran Pompa',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'all',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Spindle Tidak Bisa Mutar T-Belt Putus, Boring Saluran Pompa');
    }

    /**
     * TEST 7: Correct completion date and time.
     */
    public function test_report_displays_actual_completion_date_and_time()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Hydraulic pressure drop',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'all',
            ]));

        $response->assertStatus(200);
        $response->assertSee('09 Aug 2026');
        $response->assertSee('06:38');
    }

    /**
     * TEST 8: Excel export.
     */
    public function test_excel_export_respects_filters_and_returns_csv()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Machining Problem for Excel',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        MaintenancePlan::create([
            'machine_id' => $this->machineProduction->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Production Problem Not in Excel',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.export.excel', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'Machining',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->getContent();
        $this->assertStringContainsString('Machining Problem for Excel', $content);
        $this->assertStringNotContainsString('Production Problem Not in Excel', $content);
        $this->assertStringContainsString('L-BN.04', $content);
        $this->assertStringContainsString('BOR CNC 04', $content);
    }

    /**
     * TEST 9: PDF export.
     */
    public function test_pdf_export_returns_pdf_with_inline_disposition()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'PDF Target Problem',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.export.pdf', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'Machining',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition'));
    }

    /**
     * TEST 10: Authorization checks.
     */
    public function test_unauthorized_users_are_forbidden_from_report_and_exports()
    {
        $this->actingAs($this->unauthorizedUser)
            ->get(route('reports.index'))
            ->assertStatus(403);

        $this->actingAs($this->unauthorizedUser)
            ->get(route('reports.export.excel'))
            ->assertStatus(403);

        $this->actingAs($this->unauthorizedUser)
            ->get(route('reports.export.pdf'))
            ->assertStatus(403);
    }

    /**
     * TEST 11: No duplicate maintenance cases.
     */
    public function test_one_maintenance_case_produces_one_report_row()
    {
        MaintenancePlan::create([
            'machine_id' => $this->machineMachining->id,
            'type' => 'corrective',
            'status' => 'completed',
            'notes' => 'Single Unique Maintenance Case',
            'reported_at' => Carbon::parse('2026-08-08 08:00:00'),
            'actual_completion' => Carbon::parse('2026-08-09 06:38:00'),
            'scheduled_date' => '2026-08-08',
        ]);

        $response = $this->actingAs($this->authorizedUser)
            ->get(route('reports.index', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'department' => 'all',
            ]));

        $response->assertStatus(200);
        $response->assertSee('1 Maintenance');
    }

    /**
     * TEST 12: PDF signature section verification.
     */
    public function test_pdf_template_renders_signature_boxes()
    {
        $service = app(\App\Services\GeneralMaintenanceReportService::class);
        $reportData = $service->getReportData([
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'department' => 'all',
        ]);

        $html = view('pdf.general_maintenance_report', [
            'plans' => $reportData['plans'],
            'meta' => $reportData['meta'],
        ])->render();

        $this->assertStringContainsString('ADMIN MAINTENANCE', $html);
        $this->assertStringContainsString('KABAG MAINTENANCE', $html);
        $this->assertStringContainsString('GENERAL MAINTENANCE REPORT', $html);
    }
}
