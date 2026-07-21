<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MasterDepartment;
use App\Models\MasterMachineCategory;
use App\Models\MasterProductionArea;
use App\Services\MachineQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MachineQrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected MasterDepartment $department;
    protected MasterMachineCategory $category;
    protected MasterProductionArea $productionArea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = MasterDepartment::create([
            'name' => 'Machining Center',
            'code' => 'MC',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->category = MasterMachineCategory::create([
            'name' => 'CNC Milling',
            'code' => 'CNC-MIL',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->productionArea = MasterProductionArea::create([
            'name' => 'Workshop High Speed',
            'code' => 'W-HS',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_qr_code_is_automatically_generated_on_machine_creation_with_stable_filename(): void
    {
        $payload = [
            'code' => 'MC-TEST-01',
            'name' => 'Precision Milling Machine 01',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
        ];

        $response = $this->post(route('machines.store'), $payload);
        $response->assertRedirect(route('machines.show', 'MC-TEST-01'));

        $machine = Machine::where('code', 'MC-TEST-01')->firstOrFail();

        $this->assertNotEmpty($machine->qr_code_path);
        $this->assertEquals('storage/qr_codes/machine-' . $machine->id . '.png', $machine->qr_code_path);

        $fullPath = storage_path('app/public/qr_codes/machine-' . $machine->id . '.png');
        $this->assertTrue(File::exists($fullPath));
    }

    public function test_existing_qr_code_is_not_regenerated_when_revisited(): void
    {
        $machine = Machine::create([
            'code' => 'MC-TEST-02',
            'name' => 'Precision Milling Machine 02',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        app(MachineQrCodeService::class)->ensureExists($machine);
        $machine->refresh();

        $originalQrPath = $machine->qr_code_path;
        $originalFileMtime = filemtime(storage_path('app/public/qr_codes/machine-' . $machine->id . '.png'));

        sleep(1);

        $response = $this->get(route('machines.show', $machine->code));
        $response->assertStatus(200);

        $machine->refresh();
        $this->assertEquals($originalQrPath, $machine->qr_code_path);
        $this->assertEquals($originalFileMtime, filemtime(storage_path('app/public/qr_codes/machine-' . $machine->id . '.png')));
    }

    public function test_missing_qr_code_file_is_automatically_recovered(): void
    {
        $machine = Machine::create([
            'code' => 'MC-TEST-03',
            'name' => 'Precision Milling Machine 03',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        app(MachineQrCodeService::class)->ensureExists($machine);
        $machine->refresh();

        $filePath = storage_path('app/public/qr_codes/machine-' . $machine->id . '.png');
        $this->assertTrue(File::exists($filePath));

        // Delete file to simulate data recovery scenario
        File::delete($filePath);
        $this->assertFalse(File::exists($filePath));

        // Visiting machine passport triggers automatic QR recovery
        $response = $this->get(route('machines.show', $machine->code));
        $response->assertStatus(200);

        $this->assertTrue(File::exists($filePath));
        $response->assertSee('✓ QR Permanen');
    }

    public function test_download_qr_code_png_serves_valid_image_file(): void
    {
        $machine = Machine::create([
            'code' => 'MC-TEST-04',
            'name' => 'Precision Milling Machine 04',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        app(MachineQrCodeService::class)->ensureExists($machine);

        $response = $this->get(route('machines.qr.download', $machine->code));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'image/png');
    }

    public function test_print_qr_code_label_renders_thermal_sticker_layout(): void
    {
        $machine = Machine::create([
            'code' => 'MC-TEST-05',
            'name' => 'Cutting Plasma 05',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        app(MachineQrCodeService::class)->ensureExists($machine);

        $response = $this->get(route('machines.qr.print', $machine->code));
        $response->assertStatus(200);
        $response->assertSee('PT PERONI KARYA SENTRA');
        $response->assertSee('Cutting Plasma 05');
        $response->assertSee('MC-TEST-05');
        $response->assertSee('Scan untuk membuka Machine Passport');
        $response->assertSee('window.print()');
    }

    public function test_legacy_migration_helper_generates_qr_for_machines_without_qr(): void
    {
        $machine = Machine::create([
            'code' => 'MC-LEGACY-01',
            'name' => 'Legacy Lathe Machine',
            'department' => $this->department->name,
            'category' => $this->category->name,
            'production_area_id' => $this->productionArea->id,
            'lifecycle_status' => 'ACTIVE',
            'is_active' => true,
            'qr_code_path' => null,
        ]);

        $response = $this->get(route('machines.show', $machine->code));
        $response->assertStatus(200);
        $response->assertSee('Belum Ada QR');
        $response->assertSee('Buat QR');

        $responseGen = $this->post(route('machines.qr.generate', $machine->code));
        $responseGen->assertRedirect(route('machines.show', $machine->code));

        $machine->refresh();
        $this->assertNotEmpty($machine->qr_code_path);

        $responseAfter = $this->get(route('machines.show', $machine->code));
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('✓ QR Permanen');
        $responseAfter->assertDontSee('Buat QR');
    }
}
