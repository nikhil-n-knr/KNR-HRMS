<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;
use App\Models\Holiday; // Assuming Holiday model exists
use Carbon\Carbon;

class SandwichRuleService
{
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
            if ($this->isOffDay($date)) {
                // Look backwards for the last "Working Day"
                $prevWorkDay = $this->getPreviousWorkingDay($date);
                // Look forwards for the next "Working Day"
                $nextWorkDay = $this->getNextWorkingDay($date);

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

    protected function isOffDay(Carbon $date)
    {
        return $date->isWeekend(); 
        // In real app, check Holiday::where('date', $date)->exists();
    }

    protected function getPreviousWorkingDay(Carbon $date)
    {
        $d = $date->copy()->subDay();
        while ($this->isOffDay($d) && $startDiff = $d->diffInDays($date) < 10) { // Limit lookback
            $d->subDay();
        }
        return $d;
    }

    protected function getNextWorkingDay(Carbon $date)
    {
        $d = $date->copy()->addDay();
        while ($this->isOffDay($d) && $startDiff = $d->diffInDays($date) < 10) {
            $d->addDay();
        }
        return $d;
    }
}
