<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use App\Repositories\WarehouseRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparepartMappingTest extends TestCase
{
    use RefreshDatabase;

    protected Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the DB to get machines and repositories populated
        $this->seed();

        // Get the test machine
        $this->machine = Machine::where('code', 'CNC-08')->firstOrFail();
    }

    /**
     * Test sparepart autocomplete search by code.
     */
    public function test_search_sparepart_by_code(): void
    {
        $response = $this->json('GET', route('machines.spareparts.search', $this->machine->code), [
            'q' => 'BRG'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'code' => 'BRG-6204',
            'name' => 'Bearing 6204'
        ]);
        $response->assertJsonFragment([
            'code' => 'BRG-NU22',
            'name' => 'Cylindrical Roller Bearing NU2208'
        ]);
    }

    /**
     * Test sparepart autocomplete search by name.
     */
    public function test_search_sparepart_by_name(): void
    {
        $response = $this->json('GET', route('machines.spareparts.search', $this->machine->code), [
            'q' => 'oil'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'code' => 'HYD-OIL-46',
            'name' => 'Hydraulic Oil ISO 46'
        ]);
    }

    /**
     * Test creating a mapping successfully.
     */
    public function test_create_mapping_successfully(): void
    {
        // VBLT-A42 is not mapped to CNC-08 by default (DummyMachineSeeder maps BRG-6204, SEAL-TC-40, HYD-OIL-46)
        $this->assertDatabaseMissing('machine_required_spareparts', [
            'machine_id' => $this->machine->id,
            'warehouse_item_code' => 'VBLT-A42'
        ]);

        $response = $this->json('POST', route('machines.spareparts.store', $this->machine->code), [
            'warehouse_item_code' => 'VBLT-A42'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('mapping.code', 'VBLT-A42');
        $response->assertJsonPath('mapping.name', 'V-Belt A42');

        $this->assertDatabaseHas('machine_required_spareparts', [
            'machine_id' => $this->machine->id,
            'warehouse_item_code' => 'VBLT-A42'
        ]);
    }

    /**
     * Test duplicate mapping prevention.
     */
    public function test_duplicate_mapping_prevention(): void
    {
        // BRG-6204 is already mapped to CNC-08
        $this->assertDatabaseHas('machine_required_spareparts', [
            'machine_id' => $this->machine->id,
            'warehouse_item_code' => 'BRG-6204'
        ]);

        $response = $this->json('POST', route('machines.spareparts.store', $this->machine->code), [
            'warehouse_item_code' => 'BRG-6204'
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Sparepart ini sudah terhubung dengan mesin.');
    }

    /**
     * Test unknown warehouse item validation.
     */
    public function test_unknown_warehouse_item_validation(): void
    {
        $response = $this->json('POST', route('machines.spareparts.store', $this->machine->code), [
            'warehouse_item_code' => 'NON-EXISTENT-CODE'
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Sparepart tidak ditemukan pada Warehouse Management System.');
    }

    /**
     * Test mapping removal.
     */
    public function test_mapping_removal(): void
    {
        $mapping = MachineRequiredSparepart::where('machine_id', $this->machine->id)
            ->where('warehouse_item_code', 'BRG-6204')
            ->firstOrFail();

        $response = $this->json('DELETE', route('machines.spareparts.destroy', [
            'machine' => $this->machine->code,
            'mapping' => $mapping->id
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseMissing('machine_required_spareparts', [
            'id' => $mapping->id
        ]);
    }

    /**
     * Test live stock rendering.
     */
    public function test_live_stock_rendering_on_passport(): void
    {
        // Go to passport show page
        $response = $this->get(route('machines.show', $this->machine->code));

        $response->assertStatus(200);
        // Verify bearing details (BRG-6204 has stock 12, SEAL-TC-40 has stock 0)
        $response->assertSee('Bearing 6204');
        $response->assertSee('Kode WMS: <span class="mono font-semibold">BRG-6204</span>', false);
        $response->assertSee('Jumlah Stok: <span class="mono text-on-surface">12</span>', false);
        $response->assertSee('Tersedia');

        $response->assertSee('Seal TC 40');
        $response->assertSee('Kode WMS: <span class="mono font-semibold">SEAL-TC-40</span>', false);
        $response->assertSee('Jumlah Stok: <span class="mono text-on-surface">0</span>', false);
        $response->assertSee('Stok Habis');
    }

    /**
     * Test that WarehouseRepository is never mutated during mapping or deletion.
     */
    public function test_warehouse_repository_is_never_mutated(): void
    {
        $repo = app(WarehouseRepositoryInterface::class);

        // Before operations
        $initialItem = $repo->getItemDetails('BRG-6204');
        $initialSearch = $repo->searchItems('bearing');

        // Create mapping
        $this->json('POST', route('machines.spareparts.store', $this->machine->code), [
            'warehouse_item_code' => 'VBLT-A42'
        ]);

        // Delete mapping
        $mapping = MachineRequiredSparepart::where('machine_id', $this->machine->id)
            ->where('warehouse_item_code', 'BRG-6204')
            ->firstOrFail();

        $this->json('DELETE', route('machines.spareparts.destroy', [
            'machine' => $this->machine->code,
            'mapping' => $mapping->id
        ]));

        // After operations
        $afterItem = $repo->getItemDetails('BRG-6204');
        $afterSearch = $repo->searchItems('bearing');

        // Assert that nothing in the repository has mutated
        $this->assertEquals($initialItem, $afterItem);
        $this->assertEquals($initialSearch, $afterSearch);
    }
}
