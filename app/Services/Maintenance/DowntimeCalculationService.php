<?php

namespace App\Services\Maintenance;

use Carbon\Carbon;
use InvalidArgumentException;

class DowntimeCalculationService
{
    /**
     * Calculate the downtime duration in minutes.
     *
     * @param Carbon $reportedAt
     * @param Carbon $completedAt
     * @return int
     * @throws InvalidArgumentException
     */
    public function calculateMinutes(Carbon $reportedAt, Carbon $completedAt): int
    {
        if ($completedAt->lt($reportedAt)) {
            throw new InvalidArgumentException('Completion time cannot be earlier than reported time.');
        }

        return (int) $reportedAt->diffInMinutes($completedAt);
    }

    /**
     * Calculate the downtime duration in hours.
     *
     * @param Carbon $reportedAt
     * @param Carbon $completedAt
     * @return float
     * @throws InvalidArgumentException
     */
    public function calculateHours(Carbon $reportedAt, Carbon $completedAt): float
    {
        $minutes = $this->calculateMinutes($reportedAt, $completedAt);
        return round($minutes / 60, 2);
    }

    /**
     * Format the downtime duration into a human-readable string.
     *
     * @param Carbon $reportedAt
     * @param Carbon $completedAt
     * @return string
     * @throws InvalidArgumentException
     */
    public function formatHumanReadable(Carbon $reportedAt, Carbon $completedAt): string
    {
        $minutes = $this->calculateMinutes($reportedAt, $completedAt);

        if ($minutes < 60) {
            return "{$minutes} minutes";
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        $hoursStr = $hours === 1 ? '1 hour' : "{$hours} hours";
        $minutesStr = $remainingMinutes > 0 ? " {$remainingMinutes} minutes" : '';

        return $hoursStr . $minutesStr;
    }
}
