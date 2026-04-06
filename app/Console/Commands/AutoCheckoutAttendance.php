<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceLog;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCheckoutAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:cleanup {--date= : Specific date to cleanup (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-checkout employees who forgot to punch out after shift end + grace period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday();
        $this->info("Running Attendance Cleanup for: " . $targetDate->toDateString());

        // Find logs where out_time is NULL and status is not Absent
        // We only target logs that started on the target date.
        $openLogs = AttendanceLog::whereDate('date', $targetDate)
            ->whereNull('out_time')
            ->where('status', '!=', 'Absent')
            ->with(['employee', 'shift']) // Eager load for performance
            ->get();

        $count = 0;

        foreach ($openLogs as $log) {
            // Determine Shift End Time
            // If shift is attached to log, use it. Otherwise, look up default.
            // Note: AttendanceRegistry usually attaches a shift_id.
            
            $shift = $log->shift;
            
            if (!$shift) {
                // Determine fallback or skip? Ideally every log has a shift.
                // Fallback to employee's default if missing (rare edge case)
                 $shift = Shift::find($log->shift_id); 
            }

            if ($shift) {
                $endTimeStr = $shift->end_time; // e.g., "18:00:00"
                $logDate = Carbon::parse($log->date);
                
                // Construct full End DateTime
                // Handle overnight shifts (end time < start time scenario) if implemented, 
                // but for MVP assuming same-day end or standard overlap.
                // Assuming standard shift for now.
                
                $shiftEndTime = Carbon::parse($log->date . ' ' . $endTimeStr);
                
                // Check if Overnight shift (Start > End) - simple check
                if (Carbon::parse($shift->start_time)->gt(Carbon::parse($shift->end_time))) {
                    $shiftEndTime->addDay();
                }

                // Add Grace Period (e.g. maybe wait 4 hours after shift ends before auto-closing?)
                // The command runs at 3AM next day, so it's definitely safe to close yesterday's shifts.
                
                $this->info("Closing log for: {$log->employee->first_name} (Shift End: {$shiftEndTime->toTimeString()})");

                $log->out_time = $shiftEndTime->toTimeString();
                $log->status = 'Auto-Checkout'; // Mark specifically
                $log->save();
                
                Log::channel('attendance')->info("Auto-Checkout: {$log->employee->first_name} (ID: {$log->employee->id}) for date {$log->date}");
                $count++;
            }
        }

        $this->info("Cleanup Complete. Closed {$count} records.");
    }
}
