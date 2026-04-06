<?php

namespace App\Services\Leave;

use App\Models\Employee;
use App\Models\Holiday;
use Carbon\Carbon;

class LeaveService
{
    /**
     * Calculate the net number of leave days excluding weekends and holidays.
     * 
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return float
     */
    public function calculateNetDays($startDate, $endDate): float
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            return 0;
        }

        $totalDays = 0;
        $current = $start->copy();

        // Fetch all holidays once
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->toArray();

        while ($current->lte($end)) {
            if (!$current->isWeekend() && !in_array($current->toDateString(), $holidays)) {
                $totalDays += 1;
            }
            $current->addDay();
        }

        return (float) $totalDays;
    }

    /**
     * Check if a date range contains a Sandwich Rule violation.
     * (Simplified version: used by Attendance Processor)
     */
    public function isSandwiched(Employee $employee, Carbon $date): bool
    {
        // This logic is primarily in SandwichRuleService, 
        // but LeaveService can provide helpers if needed for balance adjustments.
        return false; 
    }
}
