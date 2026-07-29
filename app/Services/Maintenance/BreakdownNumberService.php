<?php

namespace App\Services\Maintenance;

use App\Models\MaintenancePlan;
use App\Enums\MaintenancePlanType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BreakdownNumberService
{
    /**
     * Generate the next sequential breakdown number for today.
     * This is concurrency-safe by locking the queried row for update within a transaction.
     *
     * @return string
     */
    public function generateNextNumber(): string
    {
        $todayPrefix = 'BD-' . Carbon::today()->format('Ymd') . '-';

        return DB::transaction(function () use ($todayPrefix) {
            $latestPlan = MaintenancePlan::where('type', MaintenancePlanType::CORRECTIVE)
                ->where('breakdown_number', 'like', $todayPrefix . '%')
                ->lockForUpdate()
                ->orderBy('breakdown_number', 'desc')
                ->first();

            if (!$latestPlan) {
                return $todayPrefix . '001';
            }

            $latestNumber = $latestPlan->breakdown_number;
            $suffix = substr($latestNumber, -3);
            $nextSequence = intval($suffix) + 1;

            return $todayPrefix . str_pad((string)$nextSequence, 3, '0', STR_PAD_LEFT);
        });
    }
}
