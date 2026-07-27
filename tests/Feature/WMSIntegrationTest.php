<?php

namespace Tests\Feature;

use App\Integrations\WMS\DTOs\SparepartItemDTO;
use App\Integrations\WMS\Services\MachineSparepartService;
use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WMSIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sparepart_item_dto_creation_and_array_conversion(): void
    {
        $dto = SparepartItemDTO::fromRecord(
            erpCode: 'PART-001',
            variantId: 10,
            name: 'Bearing SKF 6205',
            brand: 'SKF',
            unit: 'PCS',
            barcode: '899123456789',
            location: 'Rack A-01',
            supplier: 'PT Bearing Jaya',
            stock: 15,
            weeklyAverage: 1.5,
            category: 'General',
            mappingId: null,
            monthlyAverage: 2.5,
            sixMonthAverage: 3.5,
            trend: 'Increasing'
        );

        $this->assertEquals('PART-001', $dto->erpCode);
        $this->assertEquals(10, $dto->variantId);
        $this->assertTrue($dto->isAvailable);
        $this->assertFalse($dto->isOffline);
        $this->assertEquals('Available', $dto->toArray()['availability']);
        $this->assertEquals(1.5, $dto->weeklyAverage);
        $this->assertEquals(2.5, $dto->monthlyAverage);
        $this->assertEquals(3.5, $dto->sixMonthAverage);
        $this->assertEquals('Increasing', $dto->trend);
    }

    public function test_offline_fallback_dto(): void
    {
        $dto = SparepartItemDTO::offlineFallback('PART-999', isOffline: true);

        $this->assertTrue($dto->isOffline);
        $this->assertFalse($dto->isAvailable);
        $this->assertEquals('WMS Offline', $dto->toArray()['availability']);
    }

    public function test_machine_spareparts_view_service(): void
    {
        $this->seed();

        $machine = Machine::where('code', 'CNC-08')->firstOrFail();

        /** @var MachineSparepartService $service */
        $service = app(MachineSparepartService::class);
        $viewData = $service->getMachineSparepartsView($machine);

        $this->assertIsArray($viewData);
        $this->assertGreaterThan(0, count($viewData));
        $this->assertArrayHasKey('dto', $viewData[0]);
        $this->assertArrayHasKey('status', $viewData[0]);
        $this->assertArrayHasKey('shared_count', $viewData[0]);
    }
}
