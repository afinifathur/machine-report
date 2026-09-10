<?php

namespace Tests\Feature;

use App\Enums\ProcurementStatus;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\ProcurementCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementSearchAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Machine $machine;
    protected ProcurementCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }

        $this->machine = Machine::first() ?? Machine::create([
            'code' => 'TEST-MCH-01',
            'name' => 'Testing Machine Alpha',
            'department' => 'MAINTENANCE',
            'production_area' => 'MAINTENANCE',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
        ]);

        $this->category = ProcurementCategory::first() ?? ProcurementCategory::create([
            'name' => 'Mechanical Test',
            'slug' => 'mechanical-test',
            'is_active' => true,
        ]);
    }

    /**
     * TEST 1 — Basic search
     */
    public function test_basic_search_by_item_name(): void
    {
        $this->actingAs($this->adminUser);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Bearing 6205 Heavy Duty',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Replacement bearing',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0002',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Servo Motor 5kW',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Motor replacement',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0003',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'T-Belt HTD 8M',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Belt replacement',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0004',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Bearing 6306 High Speed',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Another bearing',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('procurements.index', ['search' => 'Bearing']));
        $response->assertStatus(200);
        $response->assertSee('Bearing 6205 Heavy Duty');
        $response->assertSee('Bearing 6306 High Speed');
        $response->assertDontSee('Servo Motor 5kW');
        $response->assertDontSee('T-Belt HTD 8M');
    }

    /**
     * TEST 2 — Search across pagination (deep record found in page 1 of search results)
     */
    public function test_search_finds_record_originally_on_later_pages(): void
    {
        $this->actingAs($this->adminUser);

        // Create 25 generic active records
        for ($i = 1; $i <= 25; $i++) {
            $rec = ProcurementCase::create([
                'case_number' => sprintf('PC-202609-G%04d', $i),
                'machine_id' => $this->machine->id,
                'procurement_category_id' => $this->category->id,
                'item_name' => 'General Item ' . $i,
                'status' => ProcurementStatus::DRAFT,
                'current_owner' => 'Admin Maintenance',
                'description' => 'Generic description',
                'target_needed_date' => now()->toDateString(),
                'created_by' => $this->adminUser->id,
            ]);
            $rec->created_at = now()->subDays(30 - $i);
            $rec->save();
        }

        // Create a unique target record created earlier (would be on page 3 in latest() sort)
        $deepCase = ProcurementCase::create([
            'case_number' => 'PC-202609-9999',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Unique Servo Motor Fanuc',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Deep target record',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);
        $deepCase->created_at = now()->subDays(50);
        $deepCase->save();

        // Without search, page 1 (10 per page) should NOT see the deep record
        $responsePage1 = $this->get(route('procurements.index', ['tab' => 'active']));
        $responsePage1->assertStatus(200);
        $responsePage1->assertDontSee('Unique Servo Motor Fanuc');

        // With search 'Servo', it must appear on page 1 of the filtered results!
        $responseSearch = $this->get(route('procurements.index', ['tab' => 'active', 'search' => 'Servo']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Unique Servo Motor Fanuc');
        $responseSearch->assertSee('PC-202609-9999');
    }

    /**
     * TEST 3 — Search + Active tab
     */
    public function test_search_in_active_tab(): void
    {
        $this->actingAs($this->adminUser);

        // Active bearing
        ProcurementCase::create([
            'case_number' => 'PC-202609-0010',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Active Bearing 6205',
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
            'description' => 'Active case',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Closed bearing
        ProcurementCase::create([
            'case_number' => 'PC-202609-0011',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Closed Bearing 6205',
            'status' => ProcurementStatus::CLOSED,
            'current_owner' => 'System',
            'description' => 'Closed case',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('procurements.index', ['tab' => 'active', 'search' => 'Bearing']));
        $response->assertStatus(200);
        $response->assertSee('Active Bearing 6205');
        $response->assertDontSee('Closed Bearing 6205');
    }

    /**
     * TEST 4 — Search + Closed tab
     */
    public function test_search_in_closed_tab(): void
    {
        $this->actingAs($this->adminUser);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0020',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Active Bearing 6205',
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
            'description' => 'Active case',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0021',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Closed Bearing 6205',
            'status' => ProcurementStatus::CLOSED,
            'current_owner' => 'System',
            'description' => 'Closed case',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('procurements.index', ['tab' => 'closed', 'search' => 'Bearing']));
        $response->assertStatus(200);
        $response->assertSee('Closed Bearing 6205');
        $response->assertDontSee('Active Bearing 6205');
    }

    /**
     * TEST 5 — Pagination preserves search query string
     */
    public function test_pagination_preserves_search_query_string(): void
    {
        $this->actingAs($this->adminUser);

        // Create 15 matching items
        for ($i = 1; $i <= 15; $i++) {
            $item = ProcurementCase::create([
                'case_number' => sprintf('PC-202609-B%04d', $i),
                'machine_id' => $this->machine->id,
                'procurement_category_id' => $this->category->id,
                'item_name' => 'Bearing Model ' . $i,
                'status' => ProcurementStatus::DRAFT,
                'current_owner' => 'Admin Maintenance',
                'description' => 'Bearing description',
                'target_needed_date' => now()->toDateString(),
                'created_by' => $this->adminUser->id,
            ]);
            $item->created_at = now()->subMinutes(20 - $i);
            $item->save();
        }

        $responsePage1 = $this->get(route('procurements.index', ['tab' => 'active', 'search' => 'Bearing', 'page' => 1]));
        $responsePage1->assertStatus(200);
        $responsePage1->assertSee('Bearing Model 15');

        $responsePage2 = $this->get(route('procurements.index', ['tab' => 'active', 'search' => 'Bearing', 'page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('Bearing Model 1');
        // Assert pagination links preserve search=Bearing
        $responsePage2->assertSee('search=Bearing');
    }

    /**
     * TEST 6 — Empty search returns normal list
     */
    public function test_empty_search_returns_normal_list(): void
    {
        $this->actingAs($this->adminUser);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0030',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'General Part A',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('procurements.index', ['tab' => 'active', 'search' => '   ']));
        $response->assertStatus(200);
        $response->assertSee('General Part A');
    }

    /**
     * TEST 7 — No results shows empty state with reset button
     */
    public function test_no_results_displays_empty_search_state(): void
    {
        $this->actingAs($this->adminUser);

        ProcurementCase::create([
            'case_number' => 'PC-202609-0040',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'General Part A',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('procurements.index', ['tab' => 'active', 'search' => 'ImpossibleKeywordXYZ']));
        $response->assertStatus(200);
        $response->assertSee('Tidak Ditemukan');
        $response->assertSee('ImpossibleKeywordXYZ');
        $response->assertSee('Hapus Pencarian');
    }

    /**
     * TEST 8 — Combining search with existing category and status filters
     */
    public function test_combining_search_with_category_and_status_filters(): void
    {
        $this->actingAs($this->adminUser);

        $catElectrical = ProcurementCategory::create([
            'name' => 'Electrical Parts',
            'slug' => 'electrical-parts',
            'is_active' => true,
        ]);

        // Mechanical + Draft + Bearing
        ProcurementCase::create([
            'case_number' => 'PC-202609-0050',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Mechanical Bearing 6205',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Electrical + Draft + Bearing
        ProcurementCase::create([
            'case_number' => 'PC-202609-0051',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $catElectrical->id,
            'item_name' => 'Electrical Bearing Motor',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Mechanical + Processing + Bearing
        ProcurementCase::create([
            'case_number' => 'PC-202609-0052',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Mechanical Bearing In Purchasing',
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
            'description' => 'Test',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Query: category = Mechanical, status = DRAFT, search = Bearing
        $response = $this->get(route('procurements.index', [
            'tab' => 'active',
            'category' => $this->category->id,
            'status' => 'draft',
            'search' => 'Bearing'
        ]));

        $response->assertStatus(200);
        $response->assertSee('PC-202609-0050');
        $response->assertDontSee('PC-202609-0051');
        $response->assertDontSee('PC-202609-0052');
    }
}
