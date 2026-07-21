<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Models\MasterDepartment;
use App\Models\MasterMachineCategory;
use App\Repositories\WarehouseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    protected WarehouseRepositoryInterface $warehouseRepo;

    public function __construct(WarehouseRepositoryInterface $warehouseRepo)
    {
        $this->warehouseRepo = $warehouseRepo;
    }

    /**
     * Display a listing of the machines.
     */
    public function index(Request $request)
    {
        $query = Machine::query()->with(['photos', 'productionArea']);

        // 1. Lifecycle Status Filter (Default to ACTIVE)
        $statusFilter = strtoupper($request->input('status_filter', 'ACTIVE'));
        if ($statusFilter !== 'ALL') {
            if (in_array($statusFilter, ['ACTIVE', 'INACTIVE', 'RETIRED'])) {
                $query->where('lifecycle_status', $statusFilter);
                if ($statusFilter === 'ACTIVE') {
                    $query->where('is_active', true);
                } else {
                    $query->where('is_active', false);
                }
            }
        }

        // 2. Search filter (Code, Name, Model, Manufacturer)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }

        // 3. Dropdown filters
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('criticality')) {
            $query->where('criticality', $request->input('criticality'));
        }

        if ($request->filled('operational_status')) {
            $query->where('operational_status', $request->input('operational_status'));
        }

        // Sorting params
        $sortBy = $request->input('sort_by', 'code');
        $sortOrder = $request->input('sort_order', 'asc');

        // Always sort ACTIVE first, then INACTIVE, then RETIRED
        $query->orderByRaw("
            CASE lifecycle_status
                WHEN 'ACTIVE' THEN 1
                WHEN 'INACTIVE' THEN 2
                WHEN 'RETIRED' THEN 3
                ELSE 4
            END ASC
        ");

        // Within each group, apply custom or default sorting
        if ($sortBy === 'health') {
            $query->orderByRaw("
                CASE operational_status
                    WHEN 'breakdown' THEN 1
                    WHEN 'maintenance' THEN 2
                    WHEN 'stopped' THEN 3
                    WHEN 'idle' THEN 4
                    WHEN 'running' THEN 5
                    ELSE 6
                END {$sortOrder}
            ");
        } elseif ($sortBy === 'criticality') {
            $query->orderByRaw("
                CASE criticality
                    WHEN 'mission_critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END {$sortOrder}
            ");
        } else {
            $validSortColumns = ['code', 'name', 'department', 'category', 'criticality', 'operational_status', 'lifecycle_status'];
            $actualSortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'code';
            $query->orderBy($actualSortBy, $sortOrder);
        }

        if ($sortBy !== 'code') {
            $query->orderBy('code', 'asc');
        }

        // Paginate using Laravel default simple pagination (15 machines per page)
        $machines = $query->paginate(15)->withQueryString();

        // Fetch distinct filter options from active Master Tables for dynamic dropdown selections
        $departments = MasterDepartment::where('is_active', true)->orderBy('sort_order')->pluck('name')->values();
        $categories = MasterMachineCategory::where('is_active', true)->orderBy('sort_order')->pluck('name')->values();
        $criticalities = ['low', 'medium', 'high', 'mission_critical'];
        $operationalStatuses = ['running', 'idle', 'maintenance', 'breakdown', 'stopped'];

        return view('machines.index', compact(
            'machines',
            'departments',
            'categories',
            'criticalities',
            'operationalStatuses',
            'sortBy',
            'sortOrder',
            'statusFilter'
        ));
    }

    /**
     * Show the form for creating a new machine.
     */
    public function create()
    {
        $departments = MasterDepartment::where('is_active', true)->orderBy('sort_order')->get();
        $categories = MasterMachineCategory::where('is_active', true)->orderBy('sort_order')->get();
        $productionAreas = \App\Models\MasterProductionArea::where('is_active', true)->orderBy('sort_order')->get();
        $lifecycles = ['ACTIVE', 'INACTIVE', 'RETIRED'];

        return view('machines.create', compact('departments', 'categories', 'productionAreas', 'lifecycles'));
    }

    /**
     * Store a newly created machine in storage.
     */
    public function store(Request $request)
    {
        // Enforce case-insensitive uniqueness and uppercase code
        if ($request->filled('code')) {
            $request->merge(['code' => strtoupper($request->input('code'))]);
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:machines,code',
                'regex:/^[A-Z0-9\-]+$/' // alpha-numeric and dash only
            ],
            'name' => 'required|string|max:255',
            'department' => [
                'required',
                'string',
                Rule::exists('master_departments', 'name')->where('is_active', true)
            ],
            'category' => [
                'required',
                'string',
                Rule::exists('master_machine_categories', 'name')->where('is_active', true)
            ],
            'production_area_id' => 'required|exists:master_production_areas,id',
            'production_area' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'lifecycle_status' => 'required|string|in:ACTIVE,INACTIVE,RETIRED',
        ], [
            'code.regex' => 'Kode Mesin hanya boleh berisi huruf, angka, dan tanda hubung (-).',
            'code.unique' => 'Kode Mesin sudah terdaftar di sistem.',
        ]);

        // Auto-assign is_active based on lifecycle_status
        $isActive = ($validated['lifecycle_status'] === 'ACTIVE');

        $machine = Machine::create(array_merge($validated, [
            'is_active' => $isActive,
            'created_by' => auth()->id(),
            'criticality' => 'medium', // Default fallback
            'operational_status' => 'running', // Default fallback
        ]));

        app(\App\Services\MachineQrCodeService::class)->ensureExists($machine);

        return redirect()->route('machines.show', $validated['code'])
            ->with('success', "Mesin {$validated['code']} ({$validated['name']}) berhasil terdaftar.");
    }

    /**
     * Display the Machine Passport (aggregated view model).
     */
    public function show(string $code)
    {
        // Retrieve the machine identity with associated structures
        $machine = Machine::with(['components', 'requiredSpareparts', 'documents', 'photos', 'productionArea'])
            ->where('code', $code)
            ->firstOrFail();

        // Automatically recover QR Code file if missing from disk
        if (!empty($machine->qr_code_path) && !file_exists(public_path($machine->qr_code_path))) {
            app(\App\Services\MachineQrCodeService::class)->ensureExists($machine);
        }

        // Map required spareparts with WMS details and keep their local relationship id
        $sparepartsDetails = $machine->requiredSpareparts->map(function ($required) {
            $details = $this->warehouseRepo->getItemDetails($required->warehouse_item_code);
            $details['mapping_id'] = $required->id;
            return $details;
        });

        // Fetch oldest active (non-completed/non-cancelled) plan for this machine
        $activePlan = MaintenancePlan::where('machine_id', $machine->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('scheduled_date', 'asc')
            ->first();

        return view('machines.show', compact('machine', 'sparepartsDetails', 'activePlan'));
    }

    /**
     * Show the form for editing the specified machine.
     */
    public function edit(string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $departments = MasterDepartment::where('is_active', true)->orderBy('sort_order')->get();
        $categories = MasterMachineCategory::where('is_active', true)->orderBy('sort_order')->get();
        $productionAreas = \App\Models\MasterProductionArea::where('is_active', true)->orderBy('sort_order')->get();
        $lifecycles = ['ACTIVE', 'INACTIVE', 'RETIRED'];

        return view('machines.edit', compact('machine', 'departments', 'categories', 'productionAreas', 'lifecycles'));
    }

    /**
     * Update the specified machine in storage.
     */
    public function update(Request $request, string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => [
                'required',
                'string',
                Rule::exists('master_departments', 'name')->where('is_active', true)
            ],
            'category' => [
                'required',
                'string',
                Rule::exists('master_machine_categories', 'name')->where('is_active', true)
            ],
            'production_area_id' => 'required|exists:master_production_areas,id',
            'production_area' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'lifecycle_status' => 'required|string|in:ACTIVE,INACTIVE,RETIRED',
        ]);

        // Auto-assign is_active based on lifecycle_status
        $isActive = ($validated['lifecycle_status'] === 'ACTIVE');

        // Update attributes, keeping 'code' immutable
        $machine->update(array_merge($validated, [
            'is_active' => $isActive,
        ]));

        return redirect()->route('machines.show', $machine->code)
            ->with('success', "Informasi mesin {$machine->code} berhasil diperbarui.");
    }

    /**
     * Archive the specified machine in storage (No hard deletes).
     */
    public function destroy(string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        // Perform archive operation instead of physical deletion
        $machine->update([
            'is_active' => false,
            'lifecycle_status' => 'INACTIVE'
        ]);

        return redirect()->route('machines.index')
            ->with('success', "Mesin {$machine->code} telah diarsipkan dan statusnya diubah menjadi Nonaktif.");
    }
}
