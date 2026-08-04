<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Enums\MaintenancePlanType;
use Illuminate\Http\Request;

class BreakdownController extends Controller
{
    /**
     * Display the corrective maintenance dashboard.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('breakdown.view'), 403);

        $activeBreakdownsCount = MaintenancePlan::corrective()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $machinesDownCount = Machine::where('operational_status', 'breakdown')->count();

        $completedTodayCount = MaintenancePlan::corrective()
            ->where('status', 'completed')
            ->whereDate('completed_at', now()->toDateString())
            ->count();

        $avgMttr = (int) round(
            MaintenancePlan::corrective()
                ->where('status', 'completed')
                ->avg('downtime_duration') ?? 0
        );

        $query = MaintenancePlan::corrective()->with('machine');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('breakdown_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('assigned_technician', 'like', "%{$search}%")
                  ->orWhereHas('machine', function($mq) use ($search) {
                      $mq->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->input('machine_id'));
        }

        if ($request->filled('technician')) {
            $query->where('assigned_technician', 'like', "%{$request->input('technician')}%");
        }

        $plans = $query->orderBy('created_at', 'desc')->paginate(15);

        $machines = Machine::where('is_active', true)->where('lifecycle_status', 'ACTIVE')->orderBy('code')->get();
        
        $operators = \App\Models\Employee::where('employment_status', \App\Enums\EmploymentStatus::ACTIVE)
            ->where('is_assignable', true)
            ->pluck('full_name')
            ->toArray();

        return view('breakdowns.index', compact(
            'activeBreakdownsCount',
            'machinesDownCount',
            'completedTodayCount',
            'avgMttr',
            'plans',
            'machines',
            'operators'
        ));
    }
}
