<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface;
use App\Integrations\WMS\Services\MachineSparepartService;
use App\Integrations\WMS\DTOs\SparepartItemDTO;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class SparepartMonitorService
{
    public function __construct(
        protected SparepartLookupRepositoryInterface $sparepartRepo,
        protected MachineSparepartService $sparepartService
    ) {}

    /**
     * Get processed and filtered sparepart monitor dataset.
     */
    public function getMonitorData(array $filters): array
    {
        // 1. MACHINE MAPPING HEALTH DATA
        $activeMachinesQuery = Machine::where('is_active', true)
            ->where('lifecycle_status', 'ACTIVE');

        $totalMachinesCount = $activeMachinesQuery->count();

        // Mapped machines: active machines having at least one required sparepart
        $mappedMachineIds = MachineRequiredSparepart::pluck('machine_id')->unique()->toArray();
        $mappedMachinesCount = Machine::where('is_active', true)
            ->where('lifecycle_status', 'ACTIVE')
            ->whereIn('id', $mappedMachineIds)
            ->count();

        $unmappedMachinesCount = max(0, $totalMachinesCount - $mappedMachinesCount);

        // 2. SPAREPART MONITORING BASE DATA
        $mappings = MachineRequiredSparepart::with('machine')
            ->get()
            ->groupBy('warehouse_item_code');

        $erpCodes = $mappings->keys()->toArray();

        // Fetch WMS stock data for these codes
        $wmsDetailsMap = $this->sparepartRepo->getItemsDetails($erpCodes);

        $items = [];
        $statusCounts = [
            'critical' => 0,
            'reorder' => 0,
            'healthy' => 0,
            'overstock' => 0,
            'unknown' => 0,
        ];

        foreach ($mappings as $code => $machineMappings) {
            /** @var SparepartItemDTO $dto */
            $dto = $wmsDetailsMap[$code] ?? SparepartItemDTO::offlineFallback($code, isOffline: true);

            // Compute overall lead time
            $maxLeadTime = $dto->leadTimeDays ?? $machineMappings->max('lead_time_days') ?? 7;

            // Compute overall criticality (A > B > C)
            $criticalityVal = 'C';
            $criticalities = $machineMappings->pluck('maintenance_criticality')->toArray();
            if (in_array('A', $criticalities)) {
                $criticalityVal = 'A';
            } elseif (in_array('B', $criticalities)) {
                $criticalityVal = 'B';
            }

            // Resolve Stock Status using Service
            $statusInfo = $this->sparepartService->resolveStockStatus($dto, $maxLeadTime);
            $statusCode = $statusInfo['code'] ?? 'unknown';

            // Handle offline state fallback to unknown for counts
            $countKey = in_array($statusCode, ['critical', 'reorder', 'healthy', 'overstock', 'unknown']) ? $statusCode : 'unknown';
            $statusCounts[$countKey]++;

            // Build item row data
            $items[] = [
                'erp_code' => $code,
                'name' => $dto->name,
                'brand' => $dto->brand,
                'unit' => $dto->unit,
                'category' => $dto->category ?? 'General',
                'stock' => $dto->stock,
                'weekly_average' => $dto->weeklyAverage,
                'lead_time' => $maxLeadTime,
                'min_stock' => $statusInfo['min_stock'] ?? null,
                'target_stock' => $statusInfo['target_stock'] ?? null,
                'coverage' => $machineMappings->count(),
                'criticality' => $criticalityVal,
                'status' => $statusInfo,
                'machines' => $machineMappings->map(fn($m) => $m->machine)->filter(),
                'last_audit_at' => $dto->lastAuditAt,
            ];
        }

        // 3. FILTERING
        $search = $filters['search'] ?? null;
        $machineFilter = $filters['machine'] ?? $filters['machine_id'] ?? null;
        $statusFilter = $filters['status'] ?? null;
        $criticalityFilter = $filters['criticality'] ?? null;

        if (!empty($search)) {
            $searchLower = strtolower(trim($search));
            $items = array_filter($items, function ($item) use ($searchLower) {
                return str_contains(strtolower($item['erp_code']), $searchLower) ||
                       str_contains(strtolower($item['name']), $searchLower);
            });
        }

        $selectedMachine = null;
        if (!empty($machineFilter)) {
            if (is_numeric($machineFilter)) {
                $selectedMachine = Machine::find($machineFilter);
            } else {
                $selectedMachine = Machine::where('code', $machineFilter)->first();
            }

            $items = array_filter($items, function ($item) use ($machineFilter) {
                foreach ($item['machines'] as $mach) {
                    if ($mach && ($mach->id == $machineFilter || $mach->code === $machineFilter)) {
                        return true;
                    }
                }
                return false;
            });
        }

        if (!empty($statusFilter)) {
            $items = array_filter($items, function ($item) use ($statusFilter) {
                return $item['status']['code'] === $statusFilter;
            });
        }

        if (!empty($criticalityFilter)) {
            $items = array_filter($items, function ($item) use ($criticalityFilter) {
                return $item['criticality'] === $criticalityFilter;
            });
        }

        // 4. SORTING
        $statusOrder = [
            'critical' => 1,
            'reorder' => 2,
            'healthy' => 3,
            'overstock' => 4,
            'unknown' => 5,
            'offline' => 6,
        ];

        usort($items, function ($a, $b) use ($statusOrder) {
            $orderA = $statusOrder[$a['status']['code']] ?? 99;
            $orderB = $statusOrder[$b['status']['code']] ?? 99;
            
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }
            
            return strcmp($a['erp_code'], $b['erp_code']);
        });

        // 5. METADATA
        $machineLabel = 'Semua Mesin';
        if ($selectedMachine) {
            $machineLabel = "{$selectedMachine->code} — {$selectedMachine->name}";
        } elseif (!empty($machineFilter)) {
            $machineLabel = (string) $machineFilter;
        }

        $statusLabel = 'Semua Status';
        if (!empty($statusFilter)) {
            $statusMap = [
                'critical' => 'Critical (<=50% Min)',
                'reorder' => 'Reorder (<Min)',
                'healthy' => 'Healthy',
                'overstock' => 'Overstock (>Target)',
                'unknown' => 'Unknown',
            ];
            $statusLabel = $statusMap[$statusFilter] ?? ucfirst($statusFilter);
        }

        $criticalityLabel = 'Semua Kelas';
        if (!empty($criticalityFilter)) {
            $criticalityLabel = "Kelas {$criticalityFilter}";
        }

        $meta = [
            'search' => $search,
            'machine' => $machineFilter,
            'machine_label' => $machineLabel,
            'selected_machine' => $selectedMachine,
            'status' => $statusFilter,
            'status_label' => $statusLabel,
            'criticality' => $criticalityFilter,
            'criticality_label' => $criticalityLabel,
            'total_items' => count($items),
            'generated_at' => Carbon::now()->format('d M Y H:i'),
        ];

        return [
            'items' => array_values($items),
            'statusCounts' => $statusCounts,
            'totalMachinesCount' => $totalMachinesCount,
            'mappedMachinesCount' => $mappedMachinesCount,
            'unmappedMachinesCount' => $unmappedMachinesCount,
            'selectedMachine' => $selectedMachine,
            'meta' => $meta,
            'lastSyncTime' => date('Y-m-d H:i') . ' WIB',
            'dataSourceMode' => app()->environment('testing') ? 'Mock' : 'Live',
        ];
    }

    /**
     * Paginate an array of items with query string persistence.
     */
    public function paginateItems(array $items, int $perPage = 15, ?int $page = null, array $queryParams = []): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $slicedItems = array_slice($items, $offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $slicedItems,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $queryParams]
        );

        return $paginator;
    }

    /**
     * Search machines for autocomplete.
     */
    public function searchMachines(string $query, int $limit = 20): Collection
    {
        $q = trim($query);
        if ($q === '') {
            return Machine::where('is_active', true)
                ->where('lifecycle_status', 'ACTIVE')
                ->orderBy('code')
                ->limit($limit)
                ->get(['id', 'code', 'name', 'department']);
        }

        return Machine::where('is_active', true)
            ->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->orderBy('code')
            ->limit($limit)
            ->get(['id', 'code', 'name', 'department']);
    }

    /**
     * Generate Excel (CSV with UTF-8 BOM) for Sparepart Monitor.
     */
    public function generateExcel(array $items, array $meta): string
    {
        $output = fopen('php://temp', 'r+');

        // Add UTF-8 BOM for Microsoft Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Header Information
        fputcsv($output, ['PT PERONI KARYA SENTRA']);
        fputcsv($output, ['FACTORY MAINTENANCE DIVISION']);
        fputcsv($output, ['SPAREPART MONITOR REPORT']);
        fputcsv($output, []);
        fputcsv($output, ['Filter Mesin:', $meta['machine_label']]);
        fputcsv($output, ['Filter Status:', $meta['status_label']]);
        fputcsv($output, ['Filter Criticality:', $meta['criticality_label']]);
        fputcsv($output, ['Pencarian:', $meta['search'] ?: '-']);
        fputcsv($output, ['Total Item Terpantau:', $meta['total_items']]);
        fputcsv($output, ['Tanggal Ekspor:', $meta['generated_at']]);
        fputcsv($output, []);

        // Table Columns
        fputcsv($output, [
            'No',
            'Status',
            'ERP Code',
            'Nama Item',
            'Kategori / Brand',
            'Stok',
            'Satuan',
            'Weekly Avg',
            'Lead Time (Hari)',
            'Min Stock',
            'Target Stock',
            'Coverage (Mesin)',
            'Last Audit'
        ]);

        $rowNumber = 1;
        foreach ($items as $item) {
            $lastAudit = '-';
            if (!empty($item['last_audit_at'])) {
                $lastAudit = Carbon::parse($item['last_audit_at'])->format('d M Y');
            }

            fputcsv($output, [
                $rowNumber++,
                $item['status']['label'] ?? 'Unknown',
                $item['erp_code'],
                $item['name'],
                ($item['category'] ?? 'General') . ' (' . ($item['brand'] ?? '-') . ')',
                $item['stock'],
                $item['unit'],
                !is_null($item['weekly_average']) ? number_format($item['weekly_average'], 1) : '-',
                $item['lead_time'],
                !is_null($item['min_stock']) ? number_format($item['min_stock'], 1) : '-',
                !is_null($item['target_stock']) ? number_format($item['target_stock'], 1) : '-',
                $item['coverage'] . ' Mesin',
                $lastAudit
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Generate PDF output for Sparepart Monitor.
     */
    public function generatePdf(array $items, array $meta): string
    {
        $pdf = Pdf::loadView('pdf.sparepart_monitor', compact('items', 'meta'));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        return $pdf->output();
    }
}
