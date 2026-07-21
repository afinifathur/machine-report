<?php

namespace Tests\Feature;

use App\Models\Machine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeders for testing since we have full real database seeders defined
        $this->seed();
    }

    /**
     * Test machine registry lists machines, search, filters, and sort options.
     */
    public function test_machine_registry_page_lists_machines_and_filters(): void
    {
        // 1. Verify index page loads successfully
        $response = $this->get(route('machines.index'));
        $response->assertStatus(200);
        $response->assertSee('CNC-08');
        $response->assertSee('CNC-04');
        $response->assertSee('ARM-12');
        $response->assertSee('PMP-08');
        $response->assertSee('DRL-19');

        // 2. Test search capability
        $responseSearch = $this->get(route('machines.index', ['search' => 'VF-2']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('CNC-04');
        $responseSearch->assertDontSee('CNC-08');

        // 3. Test department dropdown filtering
        $responseDept = $this->get(route('machines.index', ['department' => 'Assembly Center']));
        $responseDept->assertStatus(200);
        $responseDept->assertSee('ARM-12');
        $responseDept->assertDontSee('CNC-08');

        // 4. Test category dropdown filtering
        $responseCat = $this->get(route('machines.index', ['category' => 'Pump']));
        $responseCat->assertStatus(200);
        $responseCat->assertSee('PMP-08');
        $responseCat->assertDontSee('ARM-12');

        // 5. Test criticality dropdown filtering
        $responseCrit = $this->get(route('machines.index', ['criticality' => 'mission_critical']));
        $responseCrit->assertStatus(200);
        $responseCrit->assertSee('CNC-04');
        $responseCrit->assertDontSee('CNC-08');

        // 6. Test operational status dropdown filtering
        $responseStatus = $this->get(route('machines.index', ['operational_status' => 'maintenance']));
        $responseStatus->assertStatus(200);
        $responseStatus->assertSee('ARM-12');
        $responseStatus->assertDontSee('CNC-08');

        // 7. Test transient health score sorting (ascending: breakdown CNC-08/CNC-04 should appear first)
        $responseSortHealth = $this->get(route('machines.index', ['sort_by' => 'health', 'sort_order' => 'asc']));
        $responseSortHealth->assertStatus(200);
        
        // Assert ordering by comparing relative positions in response
        $content = $responseSortHealth->getContent();
        $posCNC08 = strpos($content, 'CNC-08');
        $posDRL19 = strpos($content, 'DRL-19');
        $this->assertTrue($posCNC08 < $posDRL19, 'CNC-08 (Health: 38) should appear before DRL-19 (Health: 85)');
    }

    /**
     * Test machine passport displays identity, WMS stock integration, components, and documents.
     */
    public function test_machine_passport_page_displays_details_and_warehouse_data(): void
    {
        // Get the CNC-08 machine passport
        $response = $this->get(route('machines.show', 'CNC-08'));
        $response->assertStatus(200);

        // Verify machine identity and metadata
        $response->assertSee('CNC Milling Center');
        $response->assertSee('CNC-08');
        $response->assertSee('Siemens');
        $response->assertSee('SN-CNC08-2019');

        // Verify component list is present
        $response->assertSee('Spindle Unit');
        $response->assertSee('Motor');
        $response->assertSee('PLC');

        // Verify simulated WMS stock status details resolved from WarehouseRepository
        $response->assertSee('Bearing 6204');
        $response->assertSee('BRG-6204');
        $response->assertSee('Jumlah Stok:');
        $response->assertSee('12'); // BRG-6204 stock level
        $response->assertSee('Tersedia');

        $response->assertSee('Seal TC 40');
        $response->assertSee('SEAL-TC-40');
        $response->assertSee('Stok Habis'); // SEAL-TC-40 stock level is 0

        // Verify document section (Library ISO integration)
        $response->assertSee('Dokumentasi Mesin');
        $response->assertSee('Library ISO');
        $response->assertSee('Hubungkan Dokumen');
    }
}
