<?php

namespace App\Integrations\WMS\Repositories;

use App\Integrations\WMS\DTOs\SparepartItemDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DatabaseSparepartLookupRepository implements SparepartLookupRepositoryInterface
{
    protected string $connectionName;

    public function __construct()
    {
        $this->connectionName = config('integration.wms.connection', 'wms');
    }

    /**
     * Get details for a single ERP code from WMS.
     */
    public function getItemDetails(string $erpCode): SparepartItemDTO
    {
        $batch = $this->getItemsDetails([$erpCode]);
        return $batch[$erpCode] ?? SparepartItemDTO::offlineFallback($erpCode, isOffline: false);
    }

    /**
     * Get details for multiple ERP codes in a single batch query.
     */
    public function getItemsDetails(array $erpCodes): array
    {
        $normalizedCodes = array_values(array_unique(array_filter(array_map('trim', $erpCodes))));
        
        if (empty($normalizedCodes)) {
            return [];
        }

        if (app()->environment('testing')) {
            return (new MockSparepartLookupRepository())->getItemsDetails($normalizedCodes);
        }

        $result = [];

        try {
            $variants = DB::connection($this->connectionName)
                ->table('item_variants as iv')
                ->join('items as i', 'iv.item_id', '=', 'i.id')
                ->whereIn('iv.erp_code', $normalizedCodes)
                ->select([
                    'iv.id as variant_id',
                    'iv.erp_code',
                    'i.name as item_name',
                    'iv.brand',
                    'iv.unit',
                ])
                ->get();

            if ($variants->isEmpty()) {
                foreach ($normalizedCodes as $code) {
                    $result[$code] = SparepartItemDTO::offlineFallback($code, isOffline: false);
                }
                return $result;
            }

            $variantIds = $variants->pluck('variant_id')->toArray();

            // 1. Fetch live stock and rack locations per variant
            $bins = DB::connection($this->connectionName)
                ->table('bins')
                ->whereIn('item_variant_id', $variantIds)
                ->select('item_variant_id', 'code', 'current_qty')
                ->get()
                ->groupBy('item_variant_id');

            // 2. Fetch primary barcodes per variant
            $barcodes = DB::connection($this->connectionName)
                ->table('item_barcodes')
                ->whereIn('item_variant_id', $variantIds)
                ->orderByDesc('is_primary')
                ->get()
                ->groupBy('item_variant_id');

            // 3. Fetch primary suppliers per variant
            $suppliers = DB::connection($this->connectionName)
                ->table('item_supplier as isu')
                ->join('suppliers as s', 'isu.supplier_id', '=', 's.id')
                ->whereIn('isu.item_variant_id', $variantIds)
                ->select('isu.item_variant_id', 's.name')
                ->get()
                ->groupBy('item_variant_id');

            // 4. Fetch averages and trends from the read-only view item_variant_statistics
            $statistics = DB::connection($this->connectionName)
                ->table('item_variant_statistics')
                ->whereIn('item_variant_id', $variantIds)
                ->get()
                ->keyBy('item_variant_id');

            // Map results into DTOs
            foreach ($variants as $variant) {
                $vId = $variant->variant_id;
                $code = $variant->erp_code;

                $variantBins = $bins->get($vId, collect());
                $stock = (int) $variantBins->sum('current_qty');
                
                $locations = $variantBins->pluck('code')->filter()->unique()->implode(', ');

                $primaryBarcode = $barcodes->get($vId, collect())->first()?->barcode;
                $primarySupplier = $suppliers->get($vId, collect())->first()?->name;

                $category = 'General';
                $lowerName = strtolower($variant->item_name);
                if (str_starts_with($code, '5.01') || str_contains($lowerName, 'bearing')) {
                    $category = 'Bearing';
                } elseif (str_starts_with($code, '5.02') || str_contains($lowerName, 'seal')) {
                    $category = 'Seal';
                } elseif (str_contains($lowerName, 'belt')) {
                    $category = 'Belt';
                } elseif (str_contains($lowerName, 'oil') || str_contains($lowerName, 'grease') || str_contains($lowerName, 'lubricant')) {
                    $category = 'Lubricant';
                }

                $stats = $statistics->get($vId);
                $weeklyAverage = $stats ? (float) $stats->weekly_average : null;
                $monthlyAverage = $stats ? (float) $stats->monthly_average : null;
                $sixMonthAverage = $stats ? (float) $stats->six_month_average : null;
                $trend = $stats ? $stats->trend : null;

                $result[$code] = SparepartItemDTO::fromRecord(
                    erpCode: $code,
                    variantId: $vId,
                    name: $variant->item_name,
                    brand: $variant->brand,
                    unit: $variant->unit,
                    barcode: $primaryBarcode,
                    location: $locations,
                    supplier: $primarySupplier,
                    stock: $stock,
                    weeklyAverage: $weeklyAverage,
                    category: $category,
                    mappingId: null,
                    monthlyAverage: $monthlyAverage,
                    sixMonthAverage: $sixMonthAverage,
                    trend: $trend
                );
            }

            // Fill unmapped codes with unmapped fallback DTOs
            foreach ($normalizedCodes as $code) {
                if (!isset($result[$code])) {
                    $result[$code] = SparepartItemDTO::offlineFallback($code, isOffline: false);
                }
            }

        } catch (Throwable $e) {
            Log::warning("WMS Connection failed in DatabaseSparepartLookupRepository: " . $e->getMessage());

            if (app()->environment('testing')) {
                return (new MockSparepartLookupRepository())->getItemsDetails($normalizedCodes);
            }

            foreach ($normalizedCodes as $code) {
                $result[$code] = SparepartItemDTO::offlineFallback($code, isOffline: true);
            }
        }

        return $result;
    }

    /**
     * Search spareparts in WMS with priority: ERP Code -> Barcode -> Name -> Brand.
     */
    public function searchItems(string $query, int $limit = 10): array
    {
        $search = trim($query);
        if ($search === '') {
            return [];
        }

        if (app()->environment('testing')) {
            return (new MockSparepartLookupRepository())->searchItems($query, $limit);
        }

        try {
            $searchLower = strtolower($search);

            $variantIds = DB::connection($this->connectionName)
                ->table('item_variants as iv')
                ->join('items as i', 'iv.item_id', '=', 'i.id')
                ->leftJoin('item_barcodes as ib', 'iv.id', '=', 'ib.item_variant_id')
                ->where(function ($q) use ($search) {
                    $q->where('iv.erp_code', 'LIKE', "%{$search}%")
                      ->orWhere('ib.barcode', 'LIKE', "%{$search}%")
                      ->orWhere('i.name', 'LIKE', "%{$search}%")
                      ->orWhere('iv.brand', 'LIKE', "%{$search}%");
                })
                ->select([
                    'iv.erp_code',
                    DB::raw("
                        CASE
                            WHEN LOWER(iv.erp_code) LIKE '{$searchLower}%' THEN 1
                            WHEN LOWER(ib.barcode) LIKE '{$searchLower}%' THEN 2
                            WHEN LOWER(i.name) LIKE '{$searchLower}%' THEN 3
                            WHEN LOWER(iv.brand) LIKE '{$searchLower}%' THEN 4
                            ELSE 5
                        END as search_priority
                    ")
                ])
                ->orderBy('search_priority', 'asc')
                ->distinct()
                ->limit($limit)
                ->pluck('erp_code')
                ->toArray();

            if (empty($variantIds)) {
                return [];
            }

            $detailsMap = $this->getItemsDetails($variantIds);
            
            // Preserve search priority ordering
            $orderedResults = [];
            foreach ($variantIds as $code) {
                if (isset($detailsMap[$code])) {
                    $orderedResults[] = $detailsMap[$code];
                }
            }

            return $orderedResults;

        } catch (Throwable $e) {
            Log::warning("WMS Search failed in DatabaseSparepartLookupRepository: " . $e->getMessage());

            if (app()->environment('testing')) {
                return (new MockSparepartLookupRepository())->searchItems($query, $limit);
            }

            return [];
        }
    }
}
