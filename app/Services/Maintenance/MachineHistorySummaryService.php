<?php

namespace App\Services\Maintenance;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Enums\MaintenancePlanType;

class MachineHistorySummaryService
{
    /**
     * Get presentation-ready history summary for a machine.
     *
     * @param Machine $machine
     * @return array
     */
    public function getSummary(Machine $machine): array
    {
        $allCorrective = MaintenancePlan::where('machine_id', $machine->id)
            ->where('type', MaintenancePlanType::CORRECTIVE)
            ->where('status', 'completed')
            ->get();

        $correctiveCount = $allCorrective->count();
        
        $avgDowntime = $correctiveCount > 0 
            ? (int) round($allCorrective->avg('downtime_duration')) 
            : null;

        $lastBreakdownPlan = MaintenancePlan::where('machine_id', $machine->id)
            ->where('type', MaintenancePlanType::CORRECTIVE)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();
        
        $lastPMPlan = MaintenancePlan::where('machine_id', $machine->id)
            ->where('type', MaintenancePlanType::PM)
            ->where('status', 'completed')
            ->latest('scheduled_date')
            ->first();

        return [
            'machine_name' => $machine->name,
            'machine_code' => $machine->code,
            'last_breakdown' => $lastBreakdownPlan ? $lastBreakdownPlan->completed_at->format('d/m/y H:i') : '-',
            'corrective_count' => $correctiveCount,
            'average_mttr' => $avgDowntime !== null ? "{$avgDowntime} Menit" : '-',
            'last_preventive' => $lastPMPlan ? $lastPMPlan->scheduled_date->format('d/m/y') : '-',
        ];
    }
}
