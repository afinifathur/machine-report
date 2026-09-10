<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use App\Models\MasterDepartment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SparepartMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected User $authorizedUser;
    protected User $unauthorizedUser;
    protected Machine $machineA;
    protected Machine $machineB;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed();

        $department = MasterDepartment::first() ?? MasterDepartment::create([
            'code' => 'MCH',
            'name' => 'Machining',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->machineA = Machine::create([
            'code' => 'L-BN.04',
            'name' => 'BOR CNC 04',
            'department' => $department->name,
            'production_area' => 'Area 1',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
            'lifecycle_status' => 'ACTIVE',
            'is_active' => true,
            'manufacturer' => 'Makino',
            'model' => 'BN-04',
            'serial_number' => 'SN-BN04',
            'installation_date' => '2020-01-01',
            'commissioning_date' => '2020-01-05',
            'vendor' => 'Makino Corp',
        ]);

        $this->machineB = Machine::create([
            'code' => 'L-BN.05',
            'name' => 'BOR CNC 05',
            'department' => $department->name,
            'production_area' => 'Area 2',
            'category' => 'CNC',
            'criticality' => 'medium',
            'operational_status' => 'running',
            'lifecycle_status' => 'ACTIVE',
            'is_active' => true,
            'manufacturer' => 'Makino',
            'model' => 'BN-05',
            'serial_number' => 'SN-BN05',
            'installation_date' => '2021-01-01',
            'commissioning_date' => '2021-01-05',
            'vendor' => 'Makino Corp',
        ]);

        // Map spareparts to Machine A and Machine B
        MachineRequiredSparepart::create([
            'machine_id' => $this->machineA->id,
            'warehouse_item_code' => 'BRG-6204',
            'quantity_required' => 2,
            'lead_time_days' => 7,
            'maintenance_criticality' => 'A',
            'notes' => 'Spindle bearing',
        ]);

        MachineRequiredSparepart::create([
            'machine_id' => $this->machineB->id,
            'warehouse_item_code' => 'VBLT-B52',
            'quantity_required' => 1,
            'lead_time_days' => 3,
            'maintenance_criticality' => 'B',
            'notes' => 'Main drive belt',
        ]);

        $this->authorizedUser = User::first() ?? User::factory()->create();
        if (!$this->authorizedUser->can('sparepart.view')) {
            $permission = Permission::firstOrCreate(['name' => 'sparepart.view']);
            $this->authorizedUser->givePermissionTo($permission);
        }

        $this->unauthorizedUser = User::factory()->create();
        $this->unauthorizedUser->syncPermissions([]);
        $this->unauthorizedUser->syncRoles([]);
    }

    /**
     * TEST 1: Machine search by code.
     * Search "L-BN.04" -> returns BOR CNC 04.
     */
    public function test_machine_search_by_code()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->getJson(route('spareparts.machines.autocomplete', ['search' => 'L-BN.04']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'code' => 'L-BN.04',
            'name' => 'BOR CNC 04',
        ]);
        $response->assertJsonMissing([
            'code' => 'L-BN.05',
        ]);
    }

    /**
     * TEST 2: Machine search by name.
     * Search "BOR CNC 04" -> returns L-BN.04.
     */
    public function test_machine_search_by_name()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->getJson(route('spareparts.machines.autocomplete', ['search' => 'BOR CNC 04']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'code' => 'L-BN.04',
            'name' => 'BOR CNC 04',
        ]);
    }

    /**
     * TEST 3: Machine selection filters spareparts.
     * Filter by Machine A -> only Machine A spareparts (BRG-6204) appear.
     */
    public function test_machine_selection_filters_spareparts()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->get(route('spareparts.index', ['machine' => $this->machineA->id]));

        $response->assertStatus(200);
        $response->assertSee('BRG-6204');
        $response->assertDontSee('VBLT-B52');
    }

    /**
     * TEST 4: Clear machine filter.
     * No machine filter -> all eligible spareparts returned.
     */
    public function test_clear_machine_filter_returns_all_spareparts()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->get(route('spareparts.index'));

        $response->assertStatus(200);
        $response->assertSee('BRG-6204');
        $response->assertSee('VBLT-B52');
    }

    /**
     * TEST 5: Excel export without filter.
     */
    public function test_excel_export_without_filter_returns_all_records()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->get(route('spareparts.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->getContent();
        $this->assertStringContainsString('BRG-6204', $content);
        $this->assertStringContainsString('VBLT-B52', $content);
        $this->assertStringContainsString('SPAREPART MONITOR REPORT', $content);
    }

    /**
     * TEST 6: Excel export with machine filter.
     */
    public function test_excel_export_with_machine_filter()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->get(route('spareparts.export.excel', ['machine' => $this->machineA->id]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('BRG-6204', $content);
        $this->assertStringNotContainsString('VBLT-B52', $content);
        $this->assertStringContainsString('L-BN.04', $content);
    }

    /**
     * TEST 7: PDF export with machine filter.
     */
    public function test_pdf_export_with_machine_filter_opens_inline()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->get(route('spareparts.export.pdf', ['machine' => $this->machineA->id]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition'));
    }

    /**
     * TEST 8: Export with multiple filters matches web data.
     */
    public function test_export_with_multiple_filters()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->get(route('spareparts.export.excel', [
                'machine' => $this->machineA->id,
                'search' => 'BRG',
                'criticality' => 'A',
            ]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('BRG-6204', $content);
        $this->assertStringNotContainsString('VBLT-B52', $content);
    }

    /**
     * TEST 9: Export ignores web pagination and exports all matching records.
     */
    public function test_export_ignores_pagination_and_exports_all_matching_records()
    {
        // Add additional mapped items to exceed typical page limit
        for ($i = 1; $i <= 20; $i++) {
            $code = "EXTRA-ITEM-{$i}";
            MachineRequiredSparepart::create([
                'machine_id' => $this->machineA->id,
                'warehouse_item_code' => $code,
                'quantity_required' => 1,
                'lead_time_days' => 5,
                'maintenance_criticality' => 'C',
            ]);
        }

        $service = app(\App\Services\SparepartMonitorService::class);
        $reportData = $service->getMonitorData(['machine' => $this->machineA->id]);

        // Check that monitor data contains all 21 items (BRG-6204 + 20 extras)
        $this->assertGreaterThanOrEqual(21, count($reportData['items']));

        // Web paginator slices to perPage
        $paginated = $service->paginateItems($reportData['items'], 15, 1);
        $this->assertCount(15, $paginated->items());

        // Excel contains all 21 items
        $excel = $service->generateExcel($reportData['items'], $reportData['meta']);
        $this->assertStringContainsString('EXTRA-ITEM-1', $excel);
        $this->assertStringContainsString('EXTRA-ITEM-20', $excel);
    }

    /**
     * TEST 10: PDF export identifies active filters in metadata.
     */
    public function test_pdf_template_contains_filter_metadata_and_signatures()
    {
        $service = app(\App\Services\SparepartMonitorService::class);
        $reportData = $service->getMonitorData(['machine' => $this->machineA->id]);

        $html = view('pdf.sparepart_monitor', [
            'items' => $reportData['items'],
            'meta' => $reportData['meta'],
        ])->render();

        $this->assertStringContainsString('SPAREPART MONITOR REPORT', $html);
        $this->assertStringContainsString('L-BN.04 — BOR CNC 04', $html);
        $this->assertStringContainsString('ADMIN GUDANG / SPAREPART', $html);
        $this->assertStringContainsString('KABAG MAINTENANCE', $html);
    }

    /**
     * TEST 11 & 12: Pagination persists filter query parameters.
     */
    public function test_pagination_persists_filter_query_params()
    {
        $service = app(\App\Services\SparepartMonitorService::class);
        $items = range(1, 35);
        $queryParams = ['machine' => $this->machineA->id, 'status' => 'critical'];

        $paginated = $service->paginateItems($items, 15, 2, $queryParams);

        $this->assertEquals(2, $paginated->currentPage());
        $this->assertEquals(35, $paginated->total());
        $this->assertStringContainsString('machine=' . $this->machineA->id, $paginated->url(2));
        $this->assertStringContainsString('status=critical', $paginated->url(2));
    }

    /**
     * TEST 13: Authorization protection.
     */
    public function test_unauthorized_users_are_forbidden()
    {
        $this->actingAs($this->unauthorizedUser)
            ->get(route('spareparts.index'))
            ->assertStatus(403);

        $this->actingAs($this->unauthorizedUser)
            ->get(route('spareparts.export.excel'))
            ->assertStatus(403);

        $this->actingAs($this->unauthorizedUser)
            ->get(route('spareparts.export.pdf'))
            ->assertStatus(403);

        $this->actingAs($this->unauthorizedUser)
            ->getJson(route('spareparts.machines.autocomplete'))
            ->assertStatus(403);
    }
}
