<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MasterProductionArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected MasterProductionArea $productionArea;
    protected Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed DB to populate master areas and other data
        $this->seed();

        // Get test user
        $this->adminUser = User::first() ?? User::factory()->create();
        $this->actingAs($this->adminUser);

        // Get production area
        $this->productionArea = MasterProductionArea::first();

        // Get master data
        $departmentName = \App\Models\MasterDepartment::first()?->name ?? 'Default Department';
        $categoryName = \App\Models\MasterMachineCategory::first()?->name ?? 'Default Category';

        // Create a test machine
        $this->machine = Machine::create([
            'code' => 'TEST-01',
            'name' => 'Test Machine 1',
            'department' => $departmentName,
            'category' => $categoryName,
            'production_area_id' => $this->productionArea->id,
        ]);

        // Fake public storage disk
        Storage::fake('public');
    }

    /**
     * Test adding a document link successfully.
     */
    public function test_document_link_store_success(): void
    {
        $response = $this->postJson(route('machines.documents.store', $this->machine->code), [
            'title' => 'Manual Book Fanuc',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/381',
            'description' => 'Official manual from Library ISO',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('link.title', 'Manual Book Fanuc');

        // Check database state
        $this->assertDatabaseHas('machine_document_links', [
            'machine_id' => $this->machine->id,
            'title' => 'Manual Book Fanuc',
            'library_url' => 'https://library.peroniks.id/documents/381',
        ]);
    }

    /**
     * Test document link deletion removes the database record.
     */
    public function test_document_link_deletion_success(): void
    {
        $link = \App\Models\MachineDocumentLink::create([
            'machine_id' => $this->machine->id,
            'title' => 'Manual Fanuc',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/381',
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson(route('machines.documents.destroy', [$this->machine->code, $link->id]));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // DB record is deleted
        $this->assertDatabaseMissing('machine_document_links', [
            'id' => $link->id,
        ]);
    }

    /**
     * Test document link validation rules.
     */
    public function test_document_link_validation_fails(): void
    {
        // Missing title and invalid URL
        $response = $this->postJson(route('machines.documents.store', $this->machine->code), [
            'title' => '',
            'document_category' => '',
            'library_url' => 'not-a-valid-url',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test photo upload, GD image compression, and JPEG conversion.
     */
    public function test_photo_upload_and_compression_success(): void
    {
        // Upload a PNG image
        $file = UploadedFile::fake()->image('overall.png', 2000, 1500);

        $response = $this->postJson(route('machines.photos.store', $this->machine->code), [
            'type' => 'overall',
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('photo.type', 'overall');

        $photo = \App\Models\MachinePhoto::where('machine_id', $this->machine->id)->first();
        $this->assertNotNull($photo);

        // Target file exists on storage disk
        Storage::disk('public')->assertExists($photo->file_path);

        // Verify the saved file is actually a valid compressed JPEG and resized
        $savedPath = Storage::disk('public')->path($photo->file_path);
        $info = getimagesize($savedPath);
        $this->assertEquals('image/jpeg', $info['mime']);
        
        // Longest side (2000px) scaled down to 1000px for gallery
        $this->assertEquals(1000, $info[0]); // width scaled to 1000
        $this->assertEquals(750, $info[1]); // height scaled proportionally (1500 / 2000 * 1000)

        // Check DB state
        $this->assertDatabaseHas('machine_photos', [
            'machine_id' => $this->machine->id,
            'type' => 'overall',
            'file_name' => 'overall.png',
        ]);
    }

    /**
     * Test photo deletion.
     */
    public function test_photo_deletion_success(): void
    {
        $file = UploadedFile::fake()->image('overall.png', 100, 100);
        $res = $this->postJson(route('machines.photos.store', $this->machine->code), [
            'type' => 'overall',
            'file' => $file,
        ]);

        $photo = \App\Models\MachinePhoto::where('machine_id', $this->machine->id)->first();
        Storage::disk('public')->assertExists($photo->file_path);

        $response = $this->deleteJson(route('machines.photos.destroy', [$this->machine->code, 'overall']));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // File is deleted
        Storage::disk('public')->assertMissing($photo->file_path);

        // DB record is deleted
        $this->assertDatabaseMissing('machine_photos', [
            'machine_id' => $this->machine->id,
            'type' => 'overall',
        ]);
    }

    /**
     * Test photo validation rules.
     */
    public function test_photo_validation_fails(): void
    {
        // Uploading a pdf to photo endpoint should fail
        $invalidFile = UploadedFile::fake()->create('manual.pdf', 1024, 'application/pdf');
        $response = $this->postJson(route('machines.photos.store', $this->machine->code), [
            'type' => 'overall',
            'file' => $invalidFile,
        ]);
        $response->assertStatus(422);

        // Too large (above 10MB)
        $largeFile = UploadedFile::fake()->create('heavy.png', 11 * 1024, 'image/png'); // 11MB
        $response = $this->postJson(route('machines.photos.store', $this->machine->code), [
            'type' => 'overall',
            'file' => $largeFile,
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test checklist integration progress updates.
     */
    public function test_checklist_progress_updates(): void
    {
        // Initially, progress count of new machine
        $initialProgress = $this->machine->completion_progress;
        
        // Add a document link (which updates has_manual to true)
        $response = $this->postJson(route('machines.documents.store', $this->machine->code), [
            'title' => 'Manual Book Fanuc',
            'document_category' => 'manual',
            'library_url' => 'https://library.peroniks.id/documents/381',
        ]);

        $response->assertStatus(200);
        
        // Assert the returned progress shows completion count increased by 1
        $progress = $response->json('completion_progress');
        $this->assertEquals($initialProgress['completed'] + 1, $progress['completed']);
        $this->assertTrue($progress['checklist']['manual_book']);
    }
}
