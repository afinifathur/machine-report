<?php

namespace App\Integrations\WMS\DTOs;

class SparepartItemDTO
{
    public function __construct(
        public readonly string $erpCode,
        public readonly ?int $variantId = null,
        public readonly string $name = 'Unknown Sparepart',
        public readonly ?string $brand = '-',
        public readonly ?string $unit = 'PCS',
        public readonly ?string $barcode = '-',
        public readonly ?string $location = '-',
        public readonly ?string $supplier = '-',
        public readonly int $stock = 0,
        public readonly ?float $weeklyAverage = null,
        public readonly ?string $category = 'General',
        public readonly bool $isAvailable = false,
        public readonly bool $isOffline = false,
        public readonly ?int $mappingId = null,
        public readonly ?float $monthlyAverage = null,
        public readonly ?float $sixMonthAverage = null,
        public readonly ?string $trend = null,
        public readonly ?int $leadTimeDays = null
    ) {}

    /**
     * Create a DTO from database records.
     */
    public static function fromRecord(
        string $erpCode,
        ?int $variantId,
        string $name,
        ?string $brand,
        ?string $unit,
        ?string $barcode,
        ?string $location,
        ?string $supplier,
        int $stock,
        ?float $weeklyAverage = null,
        ?string $category = 'General',
        ?int $mappingId = null,
        ?float $monthlyAverage = null,
        ?float $sixMonthAverage = null,
        ?string $trend = null,
        ?int $leadTimeDays = null
    ): self {
        return new self(
            erpCode: $erpCode,
            variantId: $variantId,
            name: $name,
            brand: $brand ?: '-',
            unit: $unit ?: 'PCS',
            barcode: $barcode ?: '-',
            location: $location ?: '-',
            supplier: $supplier ?: '-',
            stock: max(0, $stock),
            weeklyAverage: $weeklyAverage,
            category: $category ?: 'General',
            isAvailable: $stock > 0,
            isOffline: false,
            mappingId: $mappingId,
            monthlyAverage: $monthlyAverage,
            sixMonthAverage: $sixMonthAverage,
            trend: $trend,
            leadTimeDays: $leadTimeDays
        );
    }

    /**
     * Fallback DTO when WMS system is unreachable or item not found.
     */
    public static function offlineFallback(string $erpCode, bool $isOffline = true, ?int $mappingId = null, ?int $leadTimeDays = null): self
    {
        return new self(
            erpCode: $erpCode,
            variantId: null,
            name: $isOffline ? 'Sparepart (' . $erpCode . ')' : 'Sparepart Unmapped (' . $erpCode . ')',
            brand: '-',
            unit: 'PCS',
            barcode: '-',
            location: 'Unknown',
            supplier: 'Unknown',
            stock: 0,
            weeklyAverage: null,
            category: 'General',
            isAvailable: false,
            isOffline: $isOffline,
            mappingId: $mappingId,
            monthlyAverage: null,
            sixMonthAverage: null,
            trend: null,
            leadTimeDays: $leadTimeDays
        );
    }

    /**
     * Convert DTO to array for legacy controller/view compatibility.
     */
    public function toArray(): array
    {
        return [
            'code' => $this->erpCode,
            'erp_code' => $this->erpCode,
            'variant_id' => $this->variantId,
            'name' => $this->name,
            'brand' => $this->brand,
            'unit' => $this->unit,
            'barcode' => $this->barcode,
            'location' => $this->location,
            'supplier' => $this->supplier,
            'stock' => $this->stock,
            'weekly_average' => $this->weeklyAverage,
            'category' => $this->category,
            'availability' => $this->isOffline ? 'WMS Offline' : ($this->isAvailable ? 'Available' : 'Out of Stock'),
            'is_available' => $this->isAvailable,
            'is_offline' => $this->isOffline,
            'mapping_id' => $this->mappingId,
            'monthly_average' => $this->monthlyAverage,
            'six_month_average' => $this->sixMonthAverage,
            'trend' => $this->trend,
            'lead_time_days' => $this->leadTimeDays,
        ];
    }
}
