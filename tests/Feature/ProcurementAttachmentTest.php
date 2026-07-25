<?php

namespace Tests\Feature;

use App\Enums\ProcurementStatus;
use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Models\ProcurementCategory;
use App\Models\User;
use App\Models\ProcurementAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcurementAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $otherUser;
    protected Machine $machine;
    protected ProcurementCategory $category;
    protected ProcurementCase $case;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->seed();

        $this->adminUser = User::where('email', 'adminmtc@peroniks.com')->first() ?? User::factory()->create();
        if (!$this->adminUser->hasRole('Admin Maintenance')) {
            $this->adminUser->assignRole('Admin Maintenance');
        }

        $this->otherUser = User::factory()->create();
        if (!$this->otherUser->hasRole('Admin Sparepart')) {
            $this->otherUser->assignRole('Admin Sparepart');
        }

        $this->machine = Machine::first() ?? Machine::create([
            'code' => 'MCH-ATTACH-01',
            'name' => 'Machine Attachment Test',
            'department' => 'MAINTENANCE',
            'production_area' => 'MAINTENANCE',
            'category' => 'CNC',
            'criticality' => 'high',
            'operational_status' => 'running',
        ]);

        $this->category = ProcurementCategory::first() ?? ProcurementCategory::create([
            'name' => 'Mechanical',
            'slug' => 'mechanical',
            'is_active' => true,
        ]);

        $this->case = ProcurementCase::create([
            'case_number' => 'PC-' . now()->format('Ym') . '-0001',
            'machine_id' => $this->machine->id,
            'procurement_category_id' => $this->category->id,
            'item_name' => 'Test Item Attachments',
            'urgency' => 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => 'Test description',
            'reason' => 'Test reason',
            'target_needed_date' => now()->addDays(5)->toDateString(),
            'created_by' => $this->adminUser->id,
        ]);
    }

    /**
     * Test uploading a valid file succeeds (JPG/PDF, < 5MB).
     */
    public function test_attachment_upload_success(): void
    {
        $this->actingAs($this->adminUser);

        $file = UploadedFile::fake()->image('gearbox.jpg');

        $response = $this->post(route('procurements.attachments.upload', $this->case->id), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        
        // Assert stored in database
        $this->assertDatabaseHas('procurement_attachments', [
            'procurement_case_id' => $this->case->id,
            'uploaded_by' => $this->adminUser->id,
            'original_filename' => 'gearbox.jpg',
        ]);

        // Assert stored physically in public storage
        $attachment = ProcurementAttachment::first();
        Storage::disk('public')->assertExists('procurements/' . $attachment->stored_filename);
    }

    /**
     * Test invalid mime types are rejected.
     */
    public function test_attachment_upload_invalid_mime_rejected(): void
    {
        $this->actingAs($this->adminUser);

        // Uploading an invalid .txt file
        $file = UploadedFile::fake()->create('script.txt', 50, 'text/plain');

        $response = $this->post(route('procurements.attachments.upload', $this->case->id), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('procurement_attachments', 0);
    }

    /**
     * Test oversized files are rejected (> 5MB).
     */
    public function test_attachment_upload_oversized_rejected(): void
    {
        $this->actingAs($this->adminUser);

        // File size 6 MB (6144 KB)
        $file = UploadedFile::fake()->create('heavy_image.jpg', 6000, 'image/jpeg');

        $response = $this->post(route('procurements.attachments.upload', $this->case->id), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('procurement_attachments', 0);
    }

    /**
     * Test max limit of 10 attachments per case is enforced.
     */
    public function test_attachment_upload_limit_reached(): void
    {
        $this->actingAs($this->adminUser);

        // Upload 10 files
        for ($i = 1; $i <= 10; $i++) {
            $file = UploadedFile::fake()->image("file_{$i}.png");
            $response = $this->post(route('procurements.attachments.upload', $this->case->id), [
                'file' => $file,
            ]);
            $response->assertRedirect();
        }

        $this->assertDatabaseCount('procurement_attachments', 10);

        // Attempt the 11th file
        $extraFile = UploadedFile::fake()->image('extra.png');
        $response = $this->post(route('procurements.attachments.upload', $this->case->id), [
            'file' => $extraFile,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('procurement_attachments', 10);
    }

    /**
     * Test that attachment deletion works for authorized users.
     */
    public function test_attachment_delete_success(): void
    {
        $this->actingAs($this->adminUser);

        $file = UploadedFile::fake()->image('delete_me.png');
        $this->post(route('procurements.attachments.upload', $this->case->id), [
            'file' => $file,
        ]);

        $attachment = ProcurementAttachment::first();
        Storage::disk('public')->assertExists('procurements/' . $attachment->stored_filename);

        // Delete attachment
        $response = $this->delete(route('procurements.attachments.destroy', $attachment->id));
        $response->assertRedirect();

        // Database and storage asserts
        $this->assertDatabaseMissing('procurement_attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing('procurements/' . $attachment->stored_filename);
    }

    /**
     * Test that unauthorized users cannot delete attachments.
     */
    public function test_attachment_delete_unauthorized_rejected(): void
    {
        // 1. Uploaded by Admin User
        $this->actingAs($this->adminUser);
        $file = UploadedFile::fake()->image('secure.png');
        $this->post(route('procurements.attachments.upload', $this->case->id), [
            'file' => $file,
        ]);

        $attachment = ProcurementAttachment::first();

        // 2. Try deleting as otherUser (Admin Sparepart, not the owner/uploader)
        $this->actingAs($this->otherUser);
        $response = $this->delete(route('procurements.attachments.destroy', $attachment->id));
        $response->assertStatus(403);

        // Confirm DB and storage are untouched
        $this->assertDatabaseHas('procurement_attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertExists('procurements/' . $attachment->stored_filename);
    }

    /**
     * Test uploading a large image is resized to max 1600px and compressed.
     */
    public function test_attachment_image_is_resized_and_compressed(): void
    {
        $this->actingAs($this->adminUser);

        // Create a large image of 2000 x 1800 px
        $file = UploadedFile::fake()->image('large_gearbox.jpg', 2000, 1800);

        $response = $this->post(route('procurements.attachments.upload', $this->case->id), [
            'file' => $file,
        ]);

        $response->assertRedirect();

        $attachment = ProcurementAttachment::where('original_filename', 'large_gearbox.jpg')->first();
        $this->assertNotNull($attachment);

        // Assert file exists on disk
        Storage::disk('public')->assertExists('procurements/' . $attachment->stored_filename);

        // Get absolute path to the stored file on the fake disk
        $filePath = Storage::disk('public')->path('procurements/' . $attachment->stored_filename);

        // Assert size dimensions have been reduced
        list($width, $height) = getimagesize($filePath);
        $this->assertTrue($width <= 1600);
        $this->assertTrue($height <= 1600);
    }
}
