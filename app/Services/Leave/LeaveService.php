<?php

namespace App\Services\Leave;

use App\Models\Employee;
use App\Services\Attendance\WorkingDayResolverService;
use Carbon\Carbon;

class LeaveService
{
    protected WorkingDayResolverService $workingDayResolver;

    public function __construct(WorkingDayResolverService $workingDayResolver)
    {
        $this->workingDayResolver = $workingDayResolver;
    }

    /**
     * Calculate the net number of leave days excluding weekends and holidays.
     * 
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return float
     */
    public function calculateNetDays($startDate, $endDate, ?int $employeeId = null): float
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $employee = $employeeId ? Employee::find($employeeId) : null;

        if ($start->gt($end)) {
            return 0;
        }

        $totalDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (!$this->workingDayResolver->isNonWorkingDay($current->copy(), $employee)) {
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
