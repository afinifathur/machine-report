<?php

namespace Tests\Feature;

use App\Enums\ProcurementStatus;
use App\Enums\ProcurementUrgency;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\ProcurementCategory;
use App\Models\ProcurementAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementExtraTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $kabagUser;
    protected Machine $machine;
    protected ProcurementCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed default roles, permissions, and categories
        $this->seed();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }

        $this->kabagUser = User::where('email', 'kabagmtc@peroniks.com')->first() ?? User::factory()->create();
        if (!$this->kabagUser->hasRole('Kabag Maintenance')) {
            $this->kabagUser->assignRole('Kabag Maintenance');
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
     * Test Case Number is generated automatically in the format PC-YYYYMM-XXXX
     */
    public function test_case_number_is_generated_automatically(): void
    {
        $this->actingAs($this->adminUser);

        $payload = [
            'machine_id' => $this->machine->id,
            'item_name' => 'Gearbox Reducer Test',
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(7)->toDateString(),
            'machine_down' => 0,
            'description' => 'Detailed description of mechanical damage',
            'reason' => 'Critical spare part depletion',
        ];

        $response = $this->post(route('procurements.store'), $payload);
        $response->assertRedirect();

        $case = ProcurementCase::latest('id')->first();
        $this->assertNotNull($case);

        $yearMonth = now()->format('Ym');
        $expectedNumber = 'PC-' . $yearMonth . '-0001';
        $this->assertEquals($expectedNumber, $case->case_number);
    }

    /**
     * Test Case Number sequence resets to 0001 at the start of a new month
     */
    public function test_case_number_resets_monthly(): void
    {
        $this->actingAs($this->adminUser);

        // Simulate a case created last month (prevent month overflow when run on the 31st)
        $lastMonth = now()->day(1)->subMonth();
        $lastMonthPrefix = $lastMonth->format('Ym');
        
        $pastCase = ProcurementCase::create([
            'case_number' => 'PC-' . $lastMonthPrefix . '-0005',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Past Month Item',
            'urgency' => 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Past description',
            'reason' => 'Past reason',
            'target_needed_date' => $lastMonth->toDateString(),
            'created_by' => $this->adminUser->id,
            'created_at' => $lastMonth,
        ]);

        // Post a new case for the current month
        $payload = [
            'machine_id' => $this->machine->id,
            'item_name' => 'Current Month Item',
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(7)->toDateString(),
            'machine_down' => 1,
            'description' => 'New damage',
            'reason' => 'New reason',
        ];

        $response = $this->post(route('procurements.store'), $payload);
        $response->assertRedirect();

        $newCase = ProcurementCase::where('item_name', 'Current Month Item')->first();
        $this->assertNotNull($newCase);

        $currentPrefix = now()->format('Ym');
        $expectedNumber = 'PC-' . $currentPrefix . '-0001';
        $this->assertEquals($expectedNumber, $newCase->case_number);
    }

    /**
     * Test Case Number is immutable on updates
     */
    public function test_case_number_is_immutable(): void
    {
        $this->actingAs($this->adminUser);

        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0099',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Old Item Name',
            'urgency' => 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Old description',
            'reason' => 'Old reason',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $payload = [
            'machine_id' => $this->machine->id,
            'item_name' => 'Updated Item Name',
            'procurement_category_id' => $this->category->id,
            'urgency' => 'urgent',
            'target_needed_date' => now()->addDays(10)->toDateString(),
            'machine_down' => 1,
            'description' => 'Updated description text',
            'reason' => 'Updated reason text',
        ];

        $response = $this->put(route('procurements.update', $case->id), $payload);
        $response->assertRedirect();

        $case->refresh();
        $this->assertEquals('PC-' . now()->format('Ym') . '-0099', $case->case_number);
        $this->assertEquals('Updated Item Name', $case->item_name);
    }

    /**
     * Test Search works across different fields
     */
    public function test_search_works(): void
    {
        $this->actingAs($this->adminUser);

        // Case 1
        $case1 = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'item_name' => 'Servo Motor X1',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Faulty encoder',
            'reason' => 'Production line stopped',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Case 2
        $case2 = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0002',
            'machine_id' => $this->machine->id,
            'item_name' => 'Hydraulic Hose H5',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Leakage',
            'reason' => 'Preventive replacement',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Search for Servo
        $response = $this->get(route('procurements.index', ['search' => 'Servo']));
        $response->assertStatus(200);
        $response->assertSee('PC-' . now()->format('Ym') . '-0001');
        $response->assertDontSee('PC-' . now()->format('Ym') . '-0002');

        // Search for Preventive
        $response = $this->get(route('procurements.index', ['search' => 'Preventive']));
        $response->assertStatus(200);
        $response->assertSee('PC-' . now()->format('Ym') . '-0002');
        $response->assertDontSee('PC-' . now()->format('Ym') . '-0001');

        // Search for Kabag
        $response = $this->get(route('procurements.index', ['search' => 'Kabag']));
        $response->assertStatus(200);
        $response->assertSee('PC-' . now()->format('Ym') . '-0002');
        $response->assertDontSee('PC-' . now()->format('Ym') . '-0001');
    }

    /**
     * Test Filters work correctly (Status, Category, Owner, My Cases)
     */
    public function test_filters_work(): void
    {
        $this->actingAs($this->adminUser);

        $cat1 = ProcurementCategory::create(['name' => 'Pneumatic System', 'slug' => 'pneumatic-system']);
        $cat2 = ProcurementCategory::create(['name' => 'Electrical System', 'slug' => 'electrical-system']);

        $case1 = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $cat1->id,
            'item_name' => 'Valves',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Details',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $case2 = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0002',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $cat2->id,
            'item_name' => 'Relays',
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Details',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Filter status=pending_kabag
        $response = $this->get(route('procurements.index', ['status' => 'pending_kabag']));
        $response->assertSee('PC-' . now()->format('Ym') . '-0002');
        $response->assertDontSee('PC-' . now()->format('Ym') . '-0001');

        // Filter category=Pneumatic System
        $response = $this->get(route('procurements.index', ['category' => $cat1->id]));
        $response->assertSee('PC-' . now()->format('Ym') . '-0001');
        $response->assertDontSee('PC-' . now()->format('Ym') . '-0002');

        // Filter owner=Kabag Maintenance
        $response = $this->get(route('procurements.index', ['owner' => 'Kabag Maintenance']));
        $response->assertSee('PC-' . now()->format('Ym') . '-0002');
        $response->assertDontSee('PC-' . now()->format('Ym') . '-0001');

        // Filter my_cases=1 (Admin Maintenance logged in)
        $response = $this->get(route('procurements.index', ['my_cases' => 1]));
        $response->assertSee('PC-' . now()->format('Ym') . '-0001');
        $response->assertDontSee('PC-' . now()->format('Ym') . '-0002');
    }

    /**
     * Test draft can be edited, non-draft cannot
     */
    public function test_draft_can_be_edited_non_draft_cannot(): void
    {
        $this->actingAs($this->adminUser);

        // Draft case
        $draftCase = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Draft Item',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Details',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Non-draft case
        $nonDraftCase = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0002',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Non-Draft Item',
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Details',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $payload = [
            'machine_id' => $this->machine->id,
            'item_name' => 'New Name',
            'procurement_category_id' => $this->category->id,
            'urgency' => 'normal',
            'target_needed_date' => now()->addDays(7)->toDateString(),
            'machine_down' => 0,
            'description' => 'New description details',
            'reason' => 'New reason details',
        ];

        // 1. Edit Draft (Succeeds)
        $response = $this->put(route('procurements.update', $draftCase->id), $payload);
        $response->assertRedirect();
        $this->assertEquals('New Name', $draftCase->fresh()->item_name);

        // 2. Edit Non-Draft (Fails with 403 Forbidden)
        $response2 = $this->put(route('procurements.update', $nonDraftCase->id), $payload);
        $response2->assertStatus(403);
    }

    /**
     * Test create results in Draft and does not auto-submit
     */
    public function test_create_results_in_draft_without_auto_submit(): void
    {
        $this->actingAs($this->adminUser);

        $payload = [
            'machine_id' => $this->machine->id,
            'item_name' => 'Draft Created Item',
            'procurement_category_id' => $this->category->id,
            'urgency' => 'urgent',
            'target_needed_date' => now()->addDays(7)->toDateString(),
            'machine_down' => 1,
            'description' => 'Detailed damage specifications',
            'reason' => 'Emergency purchase',
            'action' => 'submit', // even if action=submit is passed, it must not auto-submit
        ];

        $response = $this->post(route('procurements.store'), $payload);
        $response->assertRedirect();

        $case = ProcurementCase::where('item_name', 'Draft Created Item')->first();
        $this->assertNotNull($case);
        $this->assertEquals(ProcurementStatus::DRAFT, $case->status);
        $this->assertEquals('Admin Maintenance', $case->current_owner);
    }

    /**
     * Test category dropdown displays only active categories
     */
    public function test_category_dropdown_only_lists_active_categories(): void
    {
        $this->actingAs($this->adminUser);

        $activeCat = ProcurementCategory::create(['name' => 'Active Category X', 'slug' => 'active-category-x', 'is_active' => true]);
        $inactiveCat = ProcurementCategory::create(['name' => 'Inactive Category Y', 'slug' => 'inactive-category-y', 'is_active' => false]);

        $response = $this->get(route('procurements.create'));
        $response->assertStatus(200);

        $response->assertSee('Active Category X');
        $response->assertDontSee('Inactive Category Y');
    }

    /**
     * Test that submitting locks data and attachments from edit/upload/delete
     */
    public function test_submit_locks_data_and_attachments(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create a draft case
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Locked Item Test',
            'urgency' => 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Create an attachment for deleting attempt later
        $attachment = ProcurementAttachment::create([
            'procurement_case_id' => $case->id,
            'uploaded_by' => $this->adminUser->id,
            'original_filename' => 'draft.png',
            'stored_filename' => 'draft_stored.png',
            'mime_type' => 'image/png',
            'file_size' => 1024,
        ]);

        // 2. Submit the case
        $response = $this->post(route('procurements.submit', $case->id));
        $response->assertRedirect();
        $this->assertEquals(ProcurementStatus::PENDING_KABAG, $case->fresh()->status);

        // 3. Try to update case fields (Expect 403)
        $response2 = $this->put(route('procurements.update', $case->id), [
            'machine_id' => $this->machine->id,
            'item_name' => 'Hacked Name',
            'procurement_category_id' => $this->category->id,
            'urgency' => 'urgent',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'machine_down' => 0,
            'description' => 'Hacked desc',
            'reason' => 'Hacked reason',
        ]);
        $response2->assertStatus(403);
        $this->assertEquals('Locked Item Test', $case->fresh()->item_name);

        // 4. Try to upload attachment (Expect 403)
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('post_submit.jpg');
        $response3 = $this->post(route('procurements.attachments.upload', $case->id), [
            'file' => $file,
        ]);
        $response3->assertStatus(403);

        // 5. Try to delete attachment (Expect 403)
        $response4 = $this->delete(route('procurements.attachments.destroy', $attachment->id));
        $response4->assertStatus(403);
    }

    /**
     * Test that rejection returns case to Draft and Admin Maintenance
     */
    public function test_reject_returns_to_draft(): void
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Rejected Case Test',
            'urgency' => 'normal',
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->kabagUser);

        $response = $this->post(route('procurements.reject', $case->id), [
            'reason' => 'Specifications are too vague.'
        ]);

        $response->assertRedirect();
        $case->refresh();

        $this->assertEquals(ProcurementStatus::DRAFT, $case->status);
        $this->assertEquals('Admin Maintenance', $case->current_owner);

        // Assert Approval record was created
        $this->assertDatabaseHas('approvals', [
            'procurement_case_id' => $case->id,
            'user_id' => $this->kabagUser->id,
            'decision' => \App\Enums\ApprovalDecision::REJECTED,
            'note' => 'Specifications are too vague.',
        ]);
    }

    /**
     * Test that Review Note displays on detail page
     */
    public function test_review_note_displays(): void
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Review Note Case',
            'urgency' => 'normal',
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Kabag rejects
        $this->actingAs($this->kabagUser);
        $this->post(route('procurements.reject', $case->id), [
            'reason' => 'CONVEYOR SPECIFICATION INCORRECT.'
        ]);

        // Admin views detail page
        $this->actingAs($this->adminUser);
        $response = $this->get(route('procurements.show', $case->id));
        $response->assertStatus(200);

        // Assert review note banner shows
        $response->assertSee('Review Note');
        $response->assertSee('CONVEYOR SPECIFICATION INCORRECT.');
    }

    /**
     * Test that CTA button is always visible on index page
     */
    public function test_cta_always_appears(): void
    {
        $this->actingAs($this->adminUser);

        // Case 1: When table has data
        ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Exists Item',
            'urgency' => 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->get(route('procurements.index'));
        $response->assertSee('Buat Pengadaan Baru');

        // Case 2: When table is empty
        ProcurementCase::query()->delete();
        $response2 = $this->get(route('procurements.index'));
        $response2->assertSee('Buat Pengadaan Baru');
    }
}
