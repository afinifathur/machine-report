<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\ProcurementCategory;
use App\Models\ProcurementAttachment;
use App\Models\Approval;
use App\Enums\ProcurementStatus;
use App\Enums\ProcurementUrgency;
use App\Enums\ApprovalDecision;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcurementPdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Machine $machine;
    protected ProcurementCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Seed roles & permissions
        $this->seed(PermissionSeeder::class);

        // Fetch / Create User with Admin Maintenance role
        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }

        // Create mock machine
        $this->machine = Machine::create([
            'code' => 'MCH-PDF-TEST-01',
            'name' => 'Machine PDF Test',
            'department' => 'MAINTENANCE',
            'production_area' => 'MAINTENANCE',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
        ]);

        // Create procurement category
        $this->category = ProcurementCategory::create([
            'name' => 'Electrical',
            'slug' => 'electrical',
            'is_active' => true,
        ]);
    }

    /**
     * Test print endpoint returns HTTP 200, Content-Type PDF, and inline disposition.
     */
    public function test_print_endpoint_returns_pdf_inline()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Sensor Proximity M12',
            'urgency' => ProcurementUrgency::NORMAL,
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Sensor rusak pada conveyor line 2.',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="procurement_report_' . $case->case_number . '.pdf"');
    }

    /**
     * Test print layout is defensive when there are no attachments, PO, or approvals.
     */
    public function test_print_layout_defensive_with_missing_optional_data()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-9999',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Defensive Test Item',
            'urgency' => ProcurementUrgency::NORMAL,
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test description',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
            'vendor_name' => null,
            'po_number' => null,
            'rack_location' => null,
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test print handles image attachments correctly.
     */
    public function test_print_with_image_attachments()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0002',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Relay Omron 24VDC',
            'urgency' => ProcurementUrgency::URGENT,
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
            'description' => 'Deskripsi relay',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Mock upload an image
        $file = UploadedFile::fake()->image('relay.png');
        $storedFilename = 'relay_mocked.png';
        Storage::disk('public')->put('procurements/' . $storedFilename, file_get_contents($file->getRealPath()));

        ProcurementAttachment::create([
            'procurement_case_id' => $case->id,
            'uploaded_by' => $this->adminUser->id,
            'original_filename' => 'relay.png',
            'stored_filename' => $storedFilename,
            'mime_type' => 'image/png',
            'file_size' => 1024,
            'created_at' => now(),
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test print handles non-image attachments (PDF, DOCX, XLSX) correctly.
     */
    public function test_print_with_non_image_attachments()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0003',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Sparepart Non Image',
            'urgency' => ProcurementUrgency::NORMAL,
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
            'description' => 'Deskripsi',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Add PDF attachment
        ProcurementAttachment::create([
            'procurement_case_id' => $case->id,
            'uploaded_by' => $this->adminUser->id,
            'original_filename' => 'specification.pdf',
            'stored_filename' => 'specification_mocked.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'created_at' => now(),
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test print handles completed approvals and digital signatures correctly.
     */
    public function test_print_with_completed_approvals()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0004',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Completed Approvals Item',
            'urgency' => ProcurementUrgency::NORMAL,
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
            'description' => 'Deskripsi',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Add Stage 1 Approval (Kabag)
        Approval::create([
            'procurement_case_id' => $case->id,
            'user_id' => $this->adminUser->id,
            'stage' => 1,
            'decision' => 'approved',
            'note' => 'Disetujui kabag',
        ]);

        // Add Stage 2 Approval (Director)
        Approval::create([
            'procurement_case_id' => $case->id,
            'user_id' => $this->adminUser->id,
            'stage' => 2,
            'decision' => 'approved',
            'note' => 'Disetujui direktur',
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test print handles null machine relationship defensively.
     */
    public function test_print_with_machine_null()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0005',
            'machine_id' => $this->machine->id, // Valid ID to pass DB constraint
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Null Machine Test Item',
            'urgency' => ProcurementUrgency::NORMAL,
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Sensor rusak',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Mock Route Binding to resolve mock case with null machine relation
        $mockCase = \Mockery::mock($case)->makePartial();
        $mockCase->shouldReceive('load')->andReturnSelf();
        $mockCase->shouldReceive('getAttribute')->with('machine')->andReturn(null);
        $mockCase->shouldReceive('getAttribute')->with('category')->andReturn($this->category);
        $mockCase->shouldReceive('getAttribute')->with('creator')->andReturn($this->adminUser);
        $mockCase->shouldReceive('getAttribute')->with('approvals')->andReturn(collect());
        $mockCase->shouldReceive('getAttribute')->with('attachments')->andReturn(collect());

        \Illuminate\Support\Facades\Route::bind('procurement', function () use ($mockCase) {
            return $mockCase;
        });

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test print handles mixed and many attachments correctly.
     */
    public function test_print_with_multiple_and_mixed_attachments()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0006',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Mixed Attachments Item',
            'urgency' => ProcurementUrgency::NORMAL,
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
            'description' => 'Deskripsi',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // Add 10 image attachments
        for ($i = 1; $i <= 10; $i++) {
            ProcurementAttachment::create([
                'procurement_case_id' => $case->id,
                'uploaded_by' => $this->adminUser->id,
                'original_filename' => "image_{$i}.png",
                'stored_filename' => "image_{$i}_mocked.png",
                'mime_type' => 'image/png',
                'file_size' => 1024,
                'created_at' => now(),
            ]);
            // Mock file in storage
            Storage::disk('public')->put("procurements/image_{$i}_mocked.png", "dummy image content");
        }

        // Add 2 non-image attachments
        ProcurementAttachment::create([
            'procurement_case_id' => $case->id,
            'uploaded_by' => $this->adminUser->id,
            'original_filename' => 'spec.xlsx',
            'stored_filename' => 'spec_mocked.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'file_size' => 5000,
            'created_at' => now(),
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test print handles pending approvals.
     */
    public function test_print_with_pending_approvals()
    {
        $case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0007',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Pending Approvals Item',
            'urgency' => ProcurementUrgency::NORMAL,
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'description' => 'Deskripsi',
            'target_needed_date' => now()->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);

        // No approval records added yet (pending stage 1 & 2)

        $this->actingAs($this->adminUser);

        $response = $this->get(route('procurements.print', $case->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
