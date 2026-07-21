<?php

namespace App\Repositories;

class MockWarehouseRepository implements WarehouseRepositoryInterface
{
    /**
     * Simulation of warehouse master items.
     * In a future phase, this will query the warehouse database connection directly.
     */
    protected array $mockWarehouse = [
        'BRG-6204' => [
            'name' => 'Bearing 6204',
            'stock' => 12,
            'location' => 'WH-A-RACK3',
            'supplier' => 'SKF Industries',
        ],
        'SEAL-TC-40' => [
            'name' => 'Seal TC 40',
            'stock' => 0,
            'location' => 'WH-B-RACK1',
            'supplier' => 'Nok Seals Corp',
        ],
        'HYD-OIL-46' => [
            'name' => 'Hydraulic Oil ISO 46',
            'stock' => 2,
            'location' => 'WH-C-DRUM2',
            'supplier' => 'Shell Lubricants',
        ],
        'SL-6204' => [
            'name' => 'Seal TC',
            'stock' => 0,
            'location' => 'WH-B-RACK1',
            'supplier' => 'Nok Seals Corp',
        ],
        'PMP-G1' => [
            'name' => 'Hydraulic Feed Pump Gear',
            'stock' => 5,
            'location' => 'WH-A-RACK12',
            'supplier' => 'Rexroth AG',
        ],
        'VBLT-A42' => [
            'name' => 'V-Belt A42',
            'stock' => 8,
            'location' => 'WH-D-RACK5',
            'supplier' => 'Gates Rubber Company',
        ],
        'RLY-24V' => [
            'name' => 'Control Relay 24V DC',
            'stock' => 15,
            'location' => 'WH-E-CAB1',
            'supplier' => 'Omron Corporation',
        ],
        'BRG-NU22' => [
            'name' => 'Cylindrical Roller Bearing NU2208',
            'stock' => 3,
            'location' => 'WH-A-RACK4',
            'supplier' => 'FAG Bearings',
        ],
    ];

    /**
     * Get details for a single warehouse item code.
     */
    public function getItemDetails(string $itemCode): array
    {
        $code = strtoupper(trim($itemCode));

        if (isset($this->mockWarehouse[$code])) {
            $item = $this->mockWarehouse[$code];
            return [
                'code' => $code,
                'name' => $item['name'],
                'stock' => $item['stock'],
                'location' => $item['location'],
                'supplier' => $item['supplier'],
                'availability' => $item['stock'] > 0 ? 'Available' : 'Out of Stock',
            ];
        }

        // Graceful fallback for unmapped codes
        return [
            'code' => $code,
            'name' => 'Spare Part (' . $code . ')',
            'stock' => 0,
            'location' => 'Unknown',
            'supplier' => 'Unknown',
            'availability' => 'Out of Stock',
        ];
    }

    /**
     * Get details for multiple warehouse item codes.
     */
    public function getItemsDetails(array $itemCodes): array
    {
        $results = [];
        foreach ($itemCodes as $code) {
            $results[$code] = $this->getItemDetails($code);
        }
        return $results;
    }

    /**
     * Search warehouse items by code or name.
     */
    public function searchItems(string $query): array
    {
        $search = strtolower(trim($query));
        if ($search === '') {
            return [];
        }

        $results = [];
        foreach ($this->mockWarehouse as $code => $item) {
            if (str_contains(strtolower($code), $search) || str_contains(strtolower($item['name']), $search)) {
                $results[] = [
                    'code' => $code,
                    'name' => $item['name'],
                    'stock' => $item['stock'],
                    'location' => $item['location'],
                    'supplier' => $item['supplier'],
                    'availability' => $item['stock'] > 0 ? 'Available' : 'Out of Stock',
                ];
            }
        }
        return $results;
    }
}
