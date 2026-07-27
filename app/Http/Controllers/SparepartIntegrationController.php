<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface;
use App\Integrations\WMS\Services\MachineSparepartService;
use App\Integrations\WMS\DTOs\SparepartItemDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SparepartIntegrationController extends Controller
{
    public function __construct(
        protected SparepartLookupRepositoryInterface $sparepartRepo,
        protected MachineSparepartService $sparepartService
    ) {}

    /**
     * Display the Machine Sparepart Monitor dashboard.
     */
    public function index(Request $request)
    {
        // ---------------------------------------------------------
        // A. MACHINE MAPPING HEALTH DATA
        // ---------------------------------------------------------
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

        // ---------------------------------------------------------
        // B. SPAREPART MONITORING DATA
        // ---------------------------------------------------------
        // Get all unique warehouse item codes mapped
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

            // Compute overall lead time (max of all mapped machines)
            $maxLeadTime = $machineMappings->max('lead_time_days') ?? 7;

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
            ];
        }

        // ---------------------------------------------------------
        // C. FILTERING & SEARCHING
        // ---------------------------------------------------------
        $search = $request->input('search');
        $machineFilter = $request->input('machine');
        $statusFilter = $request->input('status');
        $criticalityFilter = $request->input('criticality');

        if (!empty($search)) {
            $searchLower = strtolower($search);
            $items = array_filter($items, function ($item) use ($searchLower) {
                return str_contains(strtolower($item['erp_code']), $searchLower) ||
                       str_contains(strtolower($item['name']), $searchLower);
            });
        }

        if (!empty($machineFilter)) {
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

        // ---------------------------------------------------------
        // D. SORTING (Critical -> Reorder -> Healthy -> Overstock -> Unknown)
        // ---------------------------------------------------------
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

        // ---------------------------------------------------------
        // E. WIDGET & METRICS DATA
        // ---------------------------------------------------------
        $allMachines = Machine::where('is_active', true)->where('lifecycle_status', 'ACTIVE')->orderBy('name')->get();

        // Sync Observability
        $lastSyncTime = date('Y-m-d H:i') . ' WIB';
        $dataSourceMode = app()->environment('testing') ? 'Mock' : 'Live';

        return view('spareparts.index', compact(
            'items',
            'statusCounts',
            'totalMachinesCount',
            'mappedMachinesCount',
            'unmappedMachinesCount',
            'allMachines',
            'lastSyncTime',
            'dataSourceMode'
        ));
    }

    /**
     * Display a passport-style detail view for a specific sparepart.
     */
    public function show(string $erpCode)
    {
        $code = strtoupper(trim($erpCode));

        // Get mapping details
        $mappings = MachineRequiredSparepart::with('machine')
            ->where('warehouse_item_code', $code)
            ->get();

        if ($mappings->isEmpty()) {
            abort(404, "Sparepart mapping not found.");
        }

        // Fetch WMS detail
        $dto = $this->sparepartRepo->getItemDetails($code);

        // Calculate metrics
        $maxLeadTime = $mappings->max('lead_time_days') ?? 7;
        
        $criticalityVal = 'C';
        $criticalities = $mappings->pluck('maintenance_criticality')->toArray();
        if (in_array('A', $criticalities)) {
            $criticalityVal = 'A';
        } elseif (in_array('B', $criticalities)) {
            $criticalityVal = 'B';
        }

        $statusInfo = $this->sparepartService->resolveStockStatus($dto, $maxLeadTime);

        // Sync Observability
        $lastSyncTime = date('Y-m-d H:i') . ' WIB';
        $dataSourceMode = app()->environment('testing') ? 'Mock' : 'Live';

        return view('spareparts.show', compact(
            'dto',
            'mappings',
            'maxLeadTime',
            'criticalityVal',
            'statusInfo',
            'lastSyncTime',
            'dataSourceMode'
        ));
    }

    /**
     * Display machines without any sparepart mapping.
     */
    public function unmappedMachines()
    {
        $mappedMachineIds = MachineRequiredSparepart::pluck('machine_id')->unique()->toArray();
        
        $unmappedMachines = Machine::where('is_active', true)
            ->where('lifecycle_status', 'ACTIVE')
            ->whereNotIn('id', $mappedMachineIds)
            ->orderBy('name')
            ->get();

        return view('spareparts.unmapped', compact('unmappedMachines'));
    }
}
