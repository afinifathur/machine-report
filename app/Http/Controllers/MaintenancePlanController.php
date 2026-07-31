<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Enums\MaintenancePlanType;
use App\Services\MaintenanceReadinessService;
use App\Services\Maintenance\BreakdownNumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenancePlanController extends Controller
{
    protected MaintenanceReadinessService $readinessService;
    protected BreakdownNumberService $breakdownNumberService;

    public function __construct(
        MaintenanceReadinessService $readinessService,
        BreakdownNumberService $breakdownNumberService
    ) {
        $this->readinessService = $readinessService;
        $this->breakdownNumberService = $breakdownNumberService;
    }

    /**
     * Display the planning board list & calendar.
     */
    public function index(Request $request)
    {
        $typeFilter = $request->input('type_filter');

        $query = MaintenancePlan::with(['machine.documents', 'maintenanceTemplate.checklists', 'maintenanceTemplate.spareparts'])
            ->whereHas('machine', function($q) {
                $q->where('is_active', true)
                  ->where('lifecycle_status', 'ACTIVE');
            });

        // Filter by type using Enum values
        if ($typeFilter === MaintenancePlanType::PM->value) {
            $query->preventive();
        } elseif ($typeFilter === MaintenancePlanType::CORRECTIVE->value) {
            $query->corrective();
        }

        // Non-dynamic database filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('machine', function ($mq) use ($search) {
                    $mq->where('code', 'like', "%{$search}%")
                       ->orWhere('name', 'like', "%{$search}%");
                })->orWhereHas('maintenanceTemplate', function ($tq) use ($search) {
                    $tq->where('name', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $plans = $query->orderBy('scheduled_date', 'asc')->get();

        // Calculate and attach readiness report for each plan
        $plans->each(function ($plan) {
            if ($plan->isCorrective()) {
                $plan->readiness = [
                    'overall_status' => $plan->status === 'completed' ? 'Completed' : ($plan->assigned_technician ? 'Assigned' : 'Reported'),
                    'machine_ready' => false,
                    'machine_status_text' => 'Kerusakan (Down)',
                    'template_available' => true,
                    'checklist_available' => true,
                    'spareparts_available' => true,
                    'sparepart_details' => [],
                    'documents_available' => true,
                    'technician_assigned' => !empty($plan->assigned_technician),
                    'blockers' => [],
                    'warnings' => [],
                ];
            } else {
                $plan->readiness = $this->readinessService->getReadinessReport($plan);
            }
        });

        // Filter by dynamic readiness status in-memory
        if ($request->filled('readiness_status')) {
            $status = $request->input('readiness_status');
            $plans = $plans->filter(function ($plan) use ($status) {
                return $plan->readiness['overall_status'] === $status;
            });
        }

        // Calculate summary counters for the filter buttons
        $allPlans = MaintenancePlan::whereHas('machine', function($q) {
            $q->where('is_active', true)
              ->where('lifecycle_status', 'ACTIVE');
        })->with(['machine', 'maintenanceTemplate.spareparts'])->get();
        
        $allPlans->each(function ($p) {
            if ($p->isCorrective()) {
                $p->readiness = [
                    'overall_status' => $p->status === 'completed' ? 'Completed' : ($p->assigned_technician ? 'Assigned' : 'Reported'),
                    'machine_ready' => false,
                    'machine_status_text' => 'Kerusakan (Down)',
                    'template_available' => true,
                    'checklist_available' => true,
                    'spareparts_available' => true,
                    'sparepart_details' => [],
                    'documents_available' => true,
                    'technician_assigned' => !empty($p->assigned_technician),
                    'blockers' => [],
                    'warnings' => [],
                ];
            } else {
                $p->readiness = $this->readinessService->getReadinessReport($p);
            }
        });

        $totalCount = $allPlans->count();
        $blockedCount = $allPlans->filter(fn($p) => $p->readiness['overall_status'] === 'Blocked')->count();
        $almostReadyCount = $allPlans->filter(fn($p) => $p->readiness['overall_status'] === 'Almost Ready')->count();
        $readyCount = $allPlans->filter(fn($p) => $p->readiness['overall_status'] === 'Ready')->count();

        // Group plans scheduled for "Hari Ini" (today) or "Terdekat" (upcoming)
        $todayPlans = $plans->filter(fn($p) => $p->scheduled_date->isToday());
        $upcomingPlans = $plans->filter(fn($p) => !$p->scheduled_date->isToday() && $p->scheduled_date->isFuture());

        return view('planning.index', compact(
            'plans',
            'todayPlans',
            'upcomingPlans',
            'totalCount',
            'blockedCount',
            'almostReadyCount',
            'readyCount',
            'typeFilter'
        ));
    }

    /**
     * Show report breakdown form.
     */
    public function reportBreakdown()
    {
        $machines = Machine::where('is_active', true)->where('lifecycle_status', 'ACTIVE')->orderBy('code')->get();
        $departments = \App\Models\MasterDepartment::where('is_active', true)->orderBy('sort_order')->get();
        return view('breakdowns.create', compact('machines', 'departments'));
    }

    /**
     * Store reported breakdown and update machine status.
     */
    public function storeBreakdown(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'reported_by' => 'required|string|max:255',
            'reported_department' => 'required|string|max:255',
            'breakdown_description' => 'required|string',
            'reported_at' => 'nullable|date',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'scheduled_date' => 'nullable|date',
        ]);

        $machine = Machine::findOrFail($validated['machine_id']);
        
        $breakdownNumber = $this->breakdownNumberService->generateNextNumber();

        DB::transaction(function () use ($validated, $machine, $breakdownNumber) {
            MaintenancePlan::create([
                'machine_id' => $machine->id,
                'maintenance_template_id' => null,
                'scheduled_date' => !empty($validated['scheduled_date']) ? Carbon::parse($validated['scheduled_date']) : now(),
                'priority' => $validated['priority'] ?? 'high',
                'status' => 'reported',
                'type' => MaintenancePlanType::CORRECTIVE,
                'breakdown_number' => $breakdownNumber,
                'reported_at' => !empty($validated['reported_at']) ? Carbon::parse($validated['reported_at']) : now(),
                'reported_by' => $validated['reported_by'],
                'reported_department' => $validated['reported_department'],
                'notes' => $validated['breakdown_description'],
                'generation_source' => 'Manual',
            ]);

            $machine->update([
                'operational_status' => 'breakdown',
            ]);
        });

        return redirect()->route('planning.index')
            ->with('success', "Breakdown reported successfully: {$breakdownNumber}");
    }

    /**
     * Assign a technician to the breakdown ticket.
     */
    public function assignTechnician(Request $request, MaintenancePlan $plan)
    {
        $validated = $request->validate([
            'assigned_technician' => 'required|string|max:255',
        ]);

        if ($plan->type !== MaintenancePlanType::CORRECTIVE) {
            return redirect()->back()->with('error', 'Rencana bukan bertipe corrective.');
        }

        if ($plan->status !== 'reported') {
            return redirect()->back()->with('error', 'Status rencana harus reported untuk menunjuk teknisi.');
        }

        $plan->update([
            'assigned_technician' => $validated['assigned_technician'],
            'status' => 'assigned',
        ]);

        return redirect()->back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    /**
     * Display the detailed readiness audit for a single maintenance plan.
     */
    public function show(MaintenancePlan $plan)
    {
        $plan->load([
            'machine.documents',
            'maintenanceTemplate.checklists',
            'maintenanceTemplate.spareparts',
            'execution.answers.checklistItem',
            'execution.photos'
        ]);
        
        $report = $this->readinessService->getReadinessReport($plan);

        // Adjust overall status for reported/assigned corrective plans if needed
        if ($plan->isCorrective() && !in_array($plan->status, ['completed', 'waiting_review'])) {
            $report['overall_status'] = $plan->assigned_technician ? 'Assigned' : 'Reported';
        }

        return view('planning.show', compact('plan', 'report'));
    }

    /**
     * Update the maintenance plan target completion and scheduling details.
     */
    public function update(Request $request, MaintenancePlan $plan)
    {
        $rules = [
            'target_completion' => 'nullable|date',
            'notes' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
        ];

        // Only allow updating technician if not completed
        if ($plan->status !== 'completed') {
            $rules['assigned_technician'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        if (!empty($validated['target_completion'])) {
            $validated['target_completion'] = \Carbon\Carbon::parse($validated['target_completion']);
        }

        $plan->update($validated);

        // Handle corrective state transitions
        if ($plan->isCorrective() && $plan->status === 'reported' && !empty($plan->assigned_technician)) {
            $plan->update(['status' => 'assigned']);
        }

        return redirect()->route('planning.show', $plan->id)
            ->with('success', 'Rencana perawatan berhasil diperbarui.');
    }
}

