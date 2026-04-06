<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceLog;
use App\Models\CompOffCredit;
use Carbon\Carbon;

class ScanCompOffs extends Command
{
    protected $signature = 'attendance:scan-comp-offs {--days=7 : Number of days to look back}';
    protected $description = 'Scan recent attendance logs for weekend work and grant comp-off credits';

    public function handle()
    {
        $days = $this->option('days');
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $this->info("Scanning from {$startDate->toDateString()} to {$endDate->toDateString()}...");

        $logs = AttendanceLog::whereBetween('date', [$startDate, $endDate])
            ->get();

        $count = 0;

        foreach ($logs as $log) {
            $date = Carbon::parse($log->date);
            
            // Check if Weekend (Sat/Sun)
            if (!$date->isWeekend()) continue;

            // Proportional Grant Logic:
            $workMinutes = $log->total_work_minutes ?? 0;
            if ($workMinutes < 240) continue; // Minimum 4 hours for any credit

            $minutesEarned = ($workMinutes >= 480) ? 480 : 240; // 1 day if >= 8hrs, else 0.5 day (4hrs)

            // Check duplicate
            $exists = CompOffCredit::where('employee_id', $log->employee_id)
                ->where('date_earned', $log->date)
                ->exists();

            if (!$exists) {
                CompOffCredit::create([
                    'employee_id' => $log->employee_id,
                    'date_earned' => $log->date,
                    'minutes_earned' => $minutesEarned,
                    'expiry_date' => $date->copy()->addDays(90),
                    'status' => 'Available',
                    'notes' => 'Auto-credited by Scheduler (' . ($minutesEarned/480) . ' day)'
                ]);
                $count++;
                $this->info("Granted " . ($minutesEarned/480) . " day Comp-Off to Employee #{$log->employee_id} for {$log->date}");
            }
        }

        $this->info("Scan complete. Granted {$count} new credits.");
    }
}
