<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachinePhoto;
use App\Models\MasterProductionArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::first() ?? User::factory()->create();

        $area = MasterProductionArea::first() ?? MasterProductionArea::create([
            'code' => 'PA-TEST',
            'name' => 'Testing Area',
            'is_active' => true,
        ]);

        $this->machine = Machine::create([
            'name' => 'Testing Gallery CNC',
            'code' => 'MC-GAL-001',
            'department' => 'Machining',
            'category' => 'CNC Milling',
            'production_area_id' => $area->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        Storage::fake('public');
    }

    public function test_can_fetch_photos_via_json_index_with_filtering_and_sorting()
    {
        MachinePhoto::create([
            'machine_id' => $this->machine->id,
            'title' => 'Electrical Control Unit',
            'photo_type' => 'inspection',
            'type' => 'inspection',
            'description' => 'Main control panel internal wiring',
            'file_name' => 'electrical.jpg',
            'file_path' => "machines/{$this->machine->code}/photos/electrical.jpg",
            'uploaded_by' => $this->user->id,
            'created_at' => now()->subHours(2),
        ]);

        MachinePhoto::create([
            'machine_id' => $this->machine->id,
            'title' => 'Machine Name Plate',
            'photo_type' => 'name_plate',
            'type' => 'name_plate',
            'description' => 'Serial number plate',
            'file_name' => 'plate.jpg',
            'file_path' => "machines/{$this->machine->code}/photos/plate.jpg",
            'uploaded_by' => $this->user->id,
            'created_at' => now()->subHour(),
        ]);

        // Fetch all photos
        $response = $this->actingAs($this->user)
            ->getJson(route('machines.photos.index', $this->machine->code));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'total_count' => 2,
            ]);

        // Filter by category: name_plate
        $filterResponse = $this->actingAs($this->user)
            ->getJson(route('machines.photos.index', $this->machine->code) . '?category=name_plate');

        $filterResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'filtered_count' => 1,
            ]);

        // Search by query: Wiring
        $searchResponse = $this->actingAs($this->user)
            ->getJson(route('machines.photos.index', $this->machine->code) . '?search=wiring');

        $searchResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'filtered_count' => 1,
            ]);
    }

    public function test_can_upload_gallery_photo_with_metadata_and_generates_thumbnail()
    {
        $file = UploadedFile::fake()->image('spindle.jpg', 1200, 800);

        $response = $this->actingAs($this->user)
            ->postJson(route('machines.photos.store', $this->machine->code), [
                'title' => 'Main Spindle Motor',
                'photo_type' => 'inspection',
                'description' => 'High speed spindle assembly photo',
                'file' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Foto berhasil diunggah dan dikompresi.',
            ]);

        $this->assertDatabaseHas('machine_photos', [
            'machine_id' => $this->machine->id,
            'title' => 'Main Spindle Motor',
            'photo_type' => 'inspection',
            'uploaded_by' => $this->user->id,
        ]);

        $photo = MachinePhoto::where('machine_id', $this->machine->id)->first();
        $this->assertNotNull($photo);

        // Verify main file saved on storage disk
        Storage::disk('public')->assertExists($photo->file_path);

        // Verify thumbnail file saved on storage disk
        $dir = dirname($photo->file_path);
        $filename = basename($photo->file_path);
        $thumbPath = "{$dir}/thumbs/{$filename}";
        Storage::disk('public')->assertExists($thumbPath);
    }

    public function test_can_update_photo_metadata()
    {
        $photo = MachinePhoto::create([
            'machine_id' => $this->machine->id,
            'title' => 'Old Title',
            'photo_type' => 'other',
            'type' => 'other',
            'description' => 'Old description',
            'file_name' => 'sample.jpg',
            'file_path' => "machines/{$this->machine->code}/photos/sample.jpg",
            'uploaded_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson(route('machines.photos.update', [$this->machine->code, $photo->id]), [
                'title' => 'Updated Hydraulic Pump Title',
                'photo_type' => 'inspection',
                'description' => 'Updated description after maintenance check',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Metadata foto berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('machine_photos', [
            'id' => $photo->id,
            'title' => 'Updated Hydraulic Pump Title',
            'photo_type' => 'inspection',
            'description' => 'Updated description after maintenance check',
        ]);
    }

    public function test_can_delete_photo_and_removes_physical_and_thumbnail_files()
    {
        $file = UploadedFile::fake()->image('gearbox.jpg', 800, 600);

        $uploadRes = $this->actingAs($this->user)
            ->postJson(route('machines.photos.store', $this->machine->code), [
                'title' => 'Gearbox Inspection',
                'photo_type' => 'inspection',
                'file' => $file,
            ]);

        $photoId = $uploadRes->json('photo.id');
        $photo = MachinePhoto::findOrFail($photoId);

        $filePath = $photo->file_path;
        $dir = dirname($filePath);
        $filename = basename($filePath);
        $thumbPath = "{$dir}/thumbs/{$filename}";

        Storage::disk('public')->assertExists($filePath);
        Storage::disk('public')->assertExists($thumbPath);

        $deleteRes = $this->actingAs($this->user)
            ->deleteJson(route('machines.photos.destroy', [$this->machine->code, $photoId]));

        $deleteRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Foto berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('machine_photos', [
            'id' => $photoId,
        ]);

        Storage::disk('public')->assertMissing($filePath);
        Storage::disk('public')->assertMissing($thumbPath);
    }

    public function test_validates_photo_file_upload_format_and_size()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson(route('machines.photos.store', $this->machine->code), [
                'title' => 'PDF Document',
                'photo_type' => 'other',
                'file' => $invalidFile,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
