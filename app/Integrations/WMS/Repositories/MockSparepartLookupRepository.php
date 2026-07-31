<?php

namespace App\Integrations\WMS\Repositories;

use App\Integrations\WMS\DTOs\SparepartItemDTO;

class MockSparepartLookupRepository implements SparepartLookupRepositoryInterface
{
    protected array $mockWarehouse = [
        'BRG-6204' => [
            'variant_id' => 1,
            'name' => 'Bearing 6204',
            'brand' => 'SKF',
            'unit' => 'PCS',
            'barcode' => '8991001',
            'stock' => 12,
            'location' => 'WH-A-RACK3',
            'supplier' => 'SKF Industries',
            'weekly_average' => 14.0,
            'monthly_average' => 12.5,
            'six_month_average' => 10.0,
            'trend' => 'Increasing',
            'category' => 'Bearing',
            'lead_time_days' => 14,
        ],
        'SEAL-TC-40' => [
            'variant_id' => 2,
            'name' => 'Seal TC 40',
            'brand' => 'NOK',
            'unit' => 'PCS',
            'barcode' => '8991002',
            'stock' => 0,
            'location' => 'WH-B-RACK1',
            'supplier' => 'Nok Seals Corp',
            'weekly_average' => 5.0,
            'monthly_average' => 6.0,
            'six_month_average' => 5.5,
            'trend' => 'Decreasing',
            'category' => 'Seal',
        ],
        'HYD-OIL-46' => [
            'variant_id' => 3,
            'name' => 'Hydraulic Oil ISO 46',
            'brand' => 'Shell',
            'unit' => 'LITER',
            'barcode' => '8991003',
            'stock' => 2,
            'location' => 'WH-C-DRUM2',
            'supplier' => 'Shell Lubricants',
            'weekly_average' => 25.5,
            'monthly_average' => 25.0,
            'six_month_average' => 24.0,
            'trend' => 'Stable',
            'category' => 'Lubricant',
        ],
        'SL-6204' => [
            'variant_id' => 4,
            'name' => 'Seal TC',
            'brand' => 'NOK',
            'unit' => 'PCS',
            'barcode' => '8991004',
            'stock' => 0,
            'location' => 'WH-B-RACK1',
            'supplier' => 'Nok Seals Corp',
            'weekly_average' => 8.0,
            'monthly_average' => 8.0,
            'six_month_average' => 8.0,
            'trend' => 'Stable',
            'category' => 'Seal',
        ],
        'PMP-G1' => [
            'variant_id' => 5,
            'name' => 'Hydraulic Feed Pump Gear',
            'brand' => 'Rexroth',
            'unit' => 'UNIT',
            'barcode' => '8991005',
            'stock' => 5,
            'location' => 'WH-A-RACK12',
            'supplier' => 'Rexroth AG',
            'weekly_average' => 2.0,
            'monthly_average' => 1.8,
            'six_month_average' => 2.2,
            'trend' => 'Increasing',
            'category' => 'Pump',
        ],
        'VBLT-A42' => [
            'variant_id' => 6,
            'name' => 'V-Belt A42',
            'brand' => 'Gates',
            'unit' => 'PCS',
            'barcode' => '8991006',
            'stock' => 8,
            'location' => 'WH-D-RACK5',
            'supplier' => 'Gates Rubber Company',
            'weekly_average' => 12.0,
            'monthly_average' => 14.5,
            'six_month_average' => 15.0,
            'trend' => 'Decreasing',
            'category' => 'Belt',
            'lead_time_days' => 10,
        ],
        'RLY-24V' => [
            'variant_id' => 7,
            'name' => 'Control Relay 24V DC',
            'brand' => 'Omron',
            'unit' => 'PCS',
            'barcode' => '8991007',
            'stock' => 15,
            'location' => 'WH-E-CAB1',
            'supplier' => 'Omron Corporation',
            'weekly_average' => 20.0,
            'monthly_average' => 18.0,
            'six_month_average' => 15.0,
            'trend' => 'Increasing',
            'category' => 'Electrical',
        ],
        'BRG-NU22' => [
            'variant_id' => 8,
            'name' => 'Cylindrical Roller Bearing NU2208',
            'brand' => 'FAG',
            'unit' => 'PCS',
            'barcode' => '8991008',
            'stock' => 3,
            'location' => 'WH-A-RACK4',
            'supplier' => 'FAG Bearings',
            'weekly_average' => 4.0,
            'monthly_average' => 4.0,
            'six_month_average' => 4.5,
            'trend' => 'Stable',
            'category' => 'Bearing',
        ],
    ];

    public function getItemDetails(string $erpCode): SparepartItemDTO
    {
        $batch = $this->getItemsDetails([$erpCode]);
        return $batch[$erpCode] ?? SparepartItemDTO::offlineFallback($erpCode, isOffline: false);
    }

    public function getItemsDetails(array $erpCodes): array
    {
        $result = [];
        foreach ($erpCodes as $code) {
            $normalized = strtoupper(trim($code));
            if (isset($this->mockWarehouse[$normalized])) {
                $item = $this->mockWarehouse[$normalized];
                $result[$normalized] = SparepartItemDTO::fromRecord(
                    erpCode: $normalized,
                    variantId: $item['variant_id'],
                    name: $item['name'],
                    brand: $item['brand'],
                    unit: $item['unit'],
                    barcode: $item['barcode'],
                    location: $item['location'],
                    supplier: $item['supplier'],
                    stock: $item['stock'],
                    weeklyAverage: $item['weekly_average'] ?? null,
                    category: $item['category'] ?? 'General',
                    mappingId: null,
                    monthlyAverage: $item['monthly_average'] ?? null,
                    sixMonthAverage: $item['six_month_average'] ?? null,
                    trend: $item['trend'] ?? null,
                    leadTimeDays: $item['lead_time_days'] ?? 10
                );
            } else {
                $result[$normalized] = SparepartItemDTO::offlineFallback($normalized, isOffline: false);
            }
        }
        return $result;
    }

    public function searchItems(string $query, int $limit = 10): array
    {
        $search = strtolower(trim($query));
        if ($search === '') {
            return [];
        }

        $matchedCodes = [];
        foreach ($this->mockWarehouse as $code => $item) {
            if (
                str_contains(strtolower($code), $search) ||
                str_contains(strtolower($item['name']), $search) ||
                str_contains(strtolower($item['brand']), $search) ||
                str_contains(strtolower($item['barcode']), $search)
            ) {
                $matchedCodes[] = $code;
            }
        }

        $details = $this->getItemsDetails(array_slice($matchedCodes, 0, $limit));
        return array_values($details);
    }
}
