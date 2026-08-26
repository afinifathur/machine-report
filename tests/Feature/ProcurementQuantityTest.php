<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\ProcurementCategory;
use App\Enums\ProcurementStatus;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementQuantityTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Machine $machine;
    protected ProcurementCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }

        $this->machine = Machine::first() ?? Machine::create([
            'code' => 'MCH-QTY-TEST-99',
            'name' => 'Machine Qty Test 99',
            'department' => 'MAINTENANCE',
            'production_area' => 'MAINTENANCE',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
        ]);

        $this->category = ProcurementCategory::first() ?? ProcurementCategory::create([
            'name' => 'Electrical',
            'slug' => 'electrical',
            'is_active' => true,
        ]);
    }

    public function test_create_procurement_with_quantity_one_succeeds()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Component Qty 1',
            'quantity_required' => 1,
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test description',
            'reason' => 'Test reason',
            'sourcing_type' => 'local',
        ]);

        $response->assertStatus(302);
        $case = ProcurementCase::where('item_name', 'Component Qty 1')->firstOrFail();
        $this->assertEquals(1, $case->quantity_required);
    }

    public function test_create_procurement_with_quantity_greater_than_one_succeeds()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Component Qty 5',
            'quantity_required' => 5,
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test description',
            'reason' => 'Test reason',
            'sourcing_type' => 'local',
        ]);

        $response->assertStatus(302);
        $case = ProcurementCase::where('item_name', 'Component Qty 5')->firstOrFail();
        $this->assertEquals(5, $case->quantity_required);
    }

    public function test_create_procurement_with_quantity_zero_fails()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Component Qty 0',
            'quantity_required' => 0,
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test description',
            'reason' => 'Test reason',
            'sourcing_type' => 'local',
        ]);

        $response->assertSessionHasErrors(['quantity_required']);
        $this->assertDatabaseMissing('procurement_cases', [
            'item_name' => 'Component Qty 0',
        ]);
    }

    public function test_create_procurement_with_negative_quantity_fails()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Component Qty Negative',
            'quantity_required' => -5,
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test description',
            'reason' => 'Test reason',
            'sourcing_type' => 'local',
        ]);

        $response->assertSessionHasErrors(['quantity_required']);
        $this->assertDatabaseMissing('procurement_cases', [
            'item_name' => 'Component Qty Negative',
        ]);
    }

    public function test_create_procurement_with_decimal_quantity_fails()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('procurements.store'), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Component Qty Decimal',
            'quantity_required' => 1.5,
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Test description',
            'reason' => 'Test reason',
            'sourcing_type' => 'local',
        ]);

        $response->assertSessionHasErrors(['quantity_required']);
        $this->assertDatabaseMissing('procurement_cases', [
            'item_name' => 'Component Qty Decimal',
        ]);
    }

    public function test_quantity_appears_on_detail_page()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ymd') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Detail Component Qty 10',
            'quantity_required' => 10,
            'urgency' => 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test description',
            'reason' => 'Test reason',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.show', $case->id));
        $response->assertStatus(200);
        $response->assertSee('Jumlah Dibutuhkan');
        $response->assertSee('10 Unit');
    }

    public function test_quantity_appears_on_pdf_export()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ymd') . '-0002',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'PDF Component Qty 12',
            'quantity_required' => 12,
            'urgency' => 'normal',
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Test description',
            'reason' => 'Test reason',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_existing_procurement_with_null_quantity_still_renders_correctly()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ymd') . '-9999',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Existing Null Component',
            'quantity_required' => null,
            'urgency' => 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test description',
            'reason' => 'Test reason',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        // Show page
        $responseShow = $this->get(route('procurements.show', $case->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Jumlah Dibutuhkan');
        $responseShow->assertSee('-');

        // Edit page
        $responseEdit = $this->get(route('procurements.edit', $case->id));
        $responseEdit->assertStatus(200);

        // List page
        $responseList = $this->get(route('procurements.index'));
        $responseList->assertStatus(200);

        // PDF Print page
        $responsePdf = $this->get(route('procurements.print', $case->id));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('Content-Type', 'application/pdf');
    }
}
