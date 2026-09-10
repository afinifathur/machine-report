<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface;
use App\Integrations\WMS\Services\MachineSparepartService;
use App\Services\SparepartMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SparepartIntegrationController extends Controller
{
    public function __construct(
        protected SparepartLookupRepositoryInterface $sparepartRepo,
        protected MachineSparepartService $sparepartService,
        protected SparepartMonitorService $monitorService
    ) {}

    /**
     * Display the Machine Sparepart Monitor dashboard.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('sparepart.view'), 403);

        $filters = [
            'search' => $request->input('search'),
            'machine' => $request->input('machine', $request->input('machine_id')),
            'status' => $request->input('status'),
            'criticality' => $request->input('criticality'),
        ];

        $reportData = $this->monitorService->getMonitorData($filters);
        
        $perPage = 15;
        $paginatedItems = $this->monitorService->paginateItems(
            $reportData['items'], 
            $perPage, 
            (int) $request->input('page', 1), 
            $request->query()
        );

        $allMachines = Machine::where('is_active', true)
            ->where('lifecycle_status', 'ACTIVE')
            ->orderBy('code')
            ->get();

        return view('spareparts.index', [
            'items' => $reportData['items'],
            'paginatedItems' => $paginatedItems,
            'statusCounts' => $reportData['statusCounts'],
            'totalMachinesCount' => $reportData['totalMachinesCount'],
            'mappedMachinesCount' => $reportData['mappedMachinesCount'],
            'unmappedMachinesCount' => $reportData['unmappedMachinesCount'],
            'selectedMachine' => $reportData['selectedMachine'],
            'meta' => $reportData['meta'],
            'allMachines' => $allMachines,
            'lastSyncTime' => $reportData['lastSyncTime'],
            'dataSourceMode' => $reportData['dataSourceMode'],
        ]);
    }

    /**
     * Export Sparepart Monitor to Excel / CSV.
     */
    public function exportExcel(Request $request)
    {
        abort_unless(auth()->user()->can('sparepart.view'), 403);

        $filters = [
            'search' => $request->input('search'),
            'machine' => $request->input('machine', $request->input('machine_id')),
            'status' => $request->input('status'),
            'criticality' => $request->input('criticality'),
        ];

        $reportData = $this->monitorService->getMonitorData($filters);
        $csvContent = $this->monitorService->generateExcel($reportData['items'], $reportData['meta']);

        $filename = 'Sparepart_Monitor_' . date('Ymd_His') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export Sparepart Monitor to PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_unless(auth()->user()->can('sparepart.view'), 403);

        $filters = [
            'search' => $request->input('search'),
            'machine' => $request->input('machine', $request->input('machine_id')),
            'status' => $request->input('status'),
            'criticality' => $request->input('criticality'),
        ];

        $reportData = $this->monitorService->getMonitorData($filters);
        $pdfContent = $this->monitorService->generatePdf($reportData['items'], $reportData['meta']);

        $filename = 'Sparepart_Monitor_' . date('Ymd') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Search machines for autocomplete.
     */
    public function machineAutocomplete(Request $request)
    {
        abort_unless(auth()->user()->can('sparepart.view'), 403);

        $query = $request->input('search', $request->input('q', ''));
        $machines = $this->monitorService->searchMachines((string) $query, 20);

        $results = $machines->map(function ($m) {
            return [
                'id' => $m->id,
                'code' => $m->code,
                'name' => $m->name,
                'department' => $m->department,
                'label' => "{$m->code} — {$m->name}",
            ];
        });

        return response()->json($results);
    }

    /**
     * Display a passport-style detail view for a specific sparepart.
     */
    public function show(string $erpCode)
    {
        abort_unless(auth()->user()->can('sparepart.view'), 403);

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

        // Calculate metrics (prioritize WMS lead time, fallback to mapping database field)
        $maxLeadTime = $dto->leadTimeDays ?? $mappings->max('lead_time_days') ?? 7;
        
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
        abort_unless(auth()->user()->can('sparepart.view'), 403);

        $mappedMachineIds = MachineRequiredSparepart::pluck('machine_id')->unique()->toArray();
        
        $unmappedMachines = Machine::where('is_active', true)
            ->where('lifecycle_status', 'ACTIVE')
            ->whereNotIn('id', $mappedMachineIds)
            ->orderBy('name')
            ->get();

        return view('spareparts.unmapped', compact('unmappedMachines'));
    }
}
