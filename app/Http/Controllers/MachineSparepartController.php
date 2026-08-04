<?php

namespace App\Http\Controllers;

use App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface;
use App\Integrations\WMS\Services\MachineSparepartService;
use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MachineSparepartController extends Controller
{
    public function __construct(
        protected MachineSparepartService $sparepartService,
        protected SparepartLookupRepositoryInterface $sparepartLookupRepository
    ) {}

    public function search(Request $request, string $machineCode)
    {
        $machine = Machine::where('code', $machineCode)->firstOrFail();
        Gate::authorize('view', $machine);
        
        $query = $request->input('q', '');
        $results = $this->sparepartService->searchSpareparts($query);
        
        $mappings = \App\Models\MachineRequiredSparepart::where('machine_id', $machine->id)->get()->keyBy('warehouse_item_code');
        
        foreach ($results as &$item) {
            $code = $item['code'];
            if (isset($mappings[$code])) {
                // Keep the lead_time_days value from WMS instead of overriding it
                $item['maintenance_criticality'] = $mappings[$code]->maintenance_criticality;
            } else {
                $item['maintenance_criticality'] = null;
            }
        }
        
        return response()->json($results);
    }

    /**
     * Store machine required sparepart mapping.
     */
    public function store(Request $request, string $machineCode)
    {
        $machine = Machine::where('code', $machineCode)->firstOrFail();
        Gate::authorize('update', $machine);

        $validated = $request->validate([
            'warehouse_item_code' => 'required|string',
            'qty_per_machine' => 'nullable|integer|min:1',
            'lead_time_days' => 'nullable|integer|min:1',
            'maintenance_criticality' => 'nullable|string|in:A,B,C',
            'notes' => 'nullable|string',
        ]);

        $itemCode = strtoupper(trim($validated['warehouse_item_code']));

        // Retrieve item from WMS
        $itemDto = $this->sparepartLookupRepository->getItemDetails($itemCode);
        if (!$itemDto->isOffline && str_starts_with($itemDto->name, 'Sparepart Unmapped')) {
            return response()->json([
                'message' => 'Sparepart tidak ditemukan pada Warehouse Management System.'
            ], 422);
        }

        // Validate uniqueness: duplicate prevention
        $exists = MachineRequiredSparepart::where('machine_id', $machine->id)
            ->where('warehouse_item_code', $itemCode)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Sparepart ini sudah terhubung dengan mesin.'
            ], 422);
        }

        // Store relationship and mapping parameters (Lead Time comes from WMS)
        $mapping = MachineRequiredSparepart::create([
            'machine_id' => $machine->id,
            'warehouse_item_code' => $itemCode,
            'qty_per_machine' => $validated['qty_per_machine'] ?? 1,
            'lead_time_days' => $itemDto->leadTimeDays ?? $validated['lead_time_days'] ?? 7,
            'maintenance_criticality' => $validated['maintenance_criticality'] ?? 'C',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapping sparepart berhasil ditambahkan.',
            'mapping' => array_merge($itemDto->toArray(), [
                'mapping_id' => $mapping->id,
                'qty_per_machine' => $mapping->qty_per_machine,
                'lead_time_days' => $mapping->lead_time_days,
                'maintenance_criticality' => $mapping->maintenance_criticality,
                'notes' => $mapping->notes,
            ])
        ]);
    }

    /**
     * Update machine required sparepart mapping configuration.
     */
    public function update(Request $request, string $machineCode, MachineRequiredSparepart $mapping)
    {
        Gate::authorize('update', $mapping->machine);

        $validated = $request->validate([
            'qty_per_machine' => 'required|integer|min:1',
            'lead_time_days' => 'nullable|integer|min:1',
            'maintenance_criticality' => 'required|string|in:A,B,C',
            'notes' => 'nullable|string',
        ]);

        // Sync lead time from WMS
        $itemDto = $this->sparepartLookupRepository->getItemDetails($mapping->warehouse_item_code);
        $validated['lead_time_days'] = $itemDto->leadTimeDays ?? $mapping->lead_time_days ?? 7;

        $mapping->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi sparepart berhasil diperbarui.',
            'mapping' => $mapping
        ]);
    }

    /**
     * Remove machine required sparepart mapping relationship.
     */
    public function destroy(string $machineCode, MachineRequiredSparepart $mapping)
    {
        Gate::authorize('update', $mapping->machine);

        $mapping->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mapping sparepart berhasil dihapus.'
            ]);
        }

        return back()->with('success', 'Mapping sparepart berhasil dihapus.');
    }
}
