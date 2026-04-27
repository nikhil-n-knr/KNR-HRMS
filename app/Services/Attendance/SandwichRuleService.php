<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Carbon\Carbon;

class SandwichRuleService
{
    protected WorkingDayResolverService $workingDayResolver;

    public function __construct(WorkingDayResolverService $workingDayResolver)
    {
        $this->workingDayResolver = $workingDayResolver;
    }

    /**
     * Detect Sandwich Rule Violations.
     * Rule: If Absent on Friday (or day before holiday) AND Absent on Monday (or day after holiday),
     * then the holidays in between are treated as Absent/Leave.
     * 
     * @return array List of dates that should be deducted.
     */
    public function calculateDeductions($employeeId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $employee = Employee::find($employeeId);
        
        $deductions = [];

        // Fetch all logs and holidays in range
        $logs = AttendanceLog::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy('date'); // Key by YYYY-MM-DD

        // This is a simplified "Scan" approach.
        // Iterate through holidays/weekends.
        
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            // Check if today is a "Off" day (Weekend or Holiday)
            if ($this->isOffDay($date, $employee)) {
                // Look backwards for the last "Working Day"
                $prevWorkDay = $this->getPreviousWorkingDay($date, $employee);
                // Look forwards for the next "Working Day"
                $nextWorkDay = $this->getNextWorkingDay($date, $employee);

                if ($prevWorkDay && $nextWorkDay) {
                    $prevLog = $logs->get($prevWorkDay->toDateString());
                    $nextLog = $logs->get($nextWorkDay->toDateString());

                    $isPrevAbsent = !$prevLog || $prevLog->status === 'Absent';
                    $isNextAbsent = !$nextLog || $nextLog->status === 'Absent';

                    if ($isPrevAbsent && $isNextAbsent) {
                        $deductions[] = [
                            'date' => $date->toDateString(),
                            'reason' => "Sandwich Rule (Absent between {$prevWorkDay->toDateString()} and {$nextWorkDay->toDateString()})"
                        ];
                    }
                }
            }
        }

        return $deductions;
    }

    protected function isOffDay(Carbon $date, ?Employee $employee = null)
    {
        return $this->workingDayResolver->isNonWorkingDay($date->copy(), $employee);
    }

    protected function getPreviousWorkingDay(Carbon $date, ?Employee $employee = null)
    {
        $d = $date->copy()->subDay();
        while ($this->isOffDay($d, $employee) && $startDiff = $d->diffInDays($date) < 10) { // Limit lookback
            $d->subDay();
        }
        return $d;
    }

    protected function getNextWorkingDay(Carbon $date, ?Employee $employee = null)
    {
        $d = $date->copy()->addDay();
        while ($this->isOffDay($d, $employee) && $startDiff = $d->diffInDays($date) < 10) {
            $d->addDay();
        }
        return $d;
    }
}
