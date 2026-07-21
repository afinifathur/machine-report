<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineRequiredSparepart;
use App\Repositories\WarehouseRepositoryInterface;
use Illuminate\Http\Request;

class MachineSparepartController extends Controller
{
    protected WarehouseRepositoryInterface $warehouseRepo;

    public function __construct(WarehouseRepositoryInterface $warehouseRepo)
    {
        $this->warehouseRepo = $warehouseRepo;
    }

    /**
     * Autocomplete search for spareparts from WMS.
     */
    public function search(Request $request, string $machineCode)
    {
        // Verify machine exists
        Machine::where('code', $machineCode)->firstOrFail();
        
        $query = $request->input('q', '');
        $results = $this->warehouseRepo->searchItems($query);
        
        return response()->json($results);
    }

    /**
     * Store machine required sparepart mapping.
     */
    public function store(Request $request, string $machineCode)
    {
        $machine = Machine::where('code', $machineCode)->firstOrFail();

        $validated = $request->validate([
            'warehouse_item_code' => 'required|string',
        ]);

        $itemCode = strtoupper(trim($validated['warehouse_item_code']));

        // Retrieve item from WMS and check if it is a real item
        $item = $this->warehouseRepo->getItemDetails($itemCode);
        if ($item['location'] === 'Unknown' && $item['supplier'] === 'Unknown') {
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

        // Store relationship only (machine_id and warehouse_item_code)
        $mapping = MachineRequiredSparepart::create([
            'machine_id' => $machine->id,
            'warehouse_item_code' => $itemCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapping sparepart berhasil ditambahkan.',
            'mapping' => [
                'code' => $item['code'],
                'name' => $item['name'],
                'stock' => $item['stock'],
                'location' => $item['location'],
                'supplier' => $item['supplier'],
                'availability' => $item['availability'],
                'mapping_id' => $mapping->id,
            ]
        ]);
    }

    /**
     * Remove machine required sparepart mapping relationship.
     */
    public function destroy(string $machineCode, MachineRequiredSparepart $mapping)
    {
        // Deleting only the mapping relationship, never the inventory
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
