<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\Attendance\AttendanceRegistryService;

class AutoCheckoutAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-checkout-open-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-checkout open attendance sessions at exact shift end time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info('Running auto-checkout scan at ' . $now->toDateTimeString());

        $registry = app(AttendanceRegistryService::class);

        $openSessions = AttendanceSession::query()
            ->whereNull('out_time')
            ->with(['log.employee', 'log.shift'])
            ->get();

        $count = 0;

        foreach ($openSessions as $session) {
            $logModel = $session->log;
            $employee = $logModel?->employee;
            if (!$logModel || !$employee) {
                continue;
            }

            $shift = $logModel->shift ?: $registry->getShiftForDate($employee, Carbon::parse($logModel->date));
            if (!$shift || !$shift->start_time || !$shift->end_time) {
                continue;
            }

            $shiftStart = Carbon::parse($logModel->date->format('Y-m-d') . ' ' . $shift->start_time);
            $shiftEnd = Carbon::parse($logModel->date->format('Y-m-d') . ' ' . $shift->end_time);

            // Overnight shift support
            if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
                $shiftEnd->addDay();
            }

            if ($now->lt($shiftEnd)) {
                continue;
            }

            $outTime = $shiftEnd->copy();
            if ($outTime->lt(Carbon::parse($session->in_time))) {
                $outTime = Carbon::parse($session->in_time);
            }

            $session->update([
                'out_time' => $outTime,
                'out_ip' => 'SYSTEM_AUTO_SHIFT_END',
            ]);

            $freshLog = $logModel->fresh(['sessions', 'shift']);
            $registry->recalculateDailyTotals($freshLog);

            Log::info('Auto-Checkout Command: closed open session at shift end', [
                'employee_id' => $employee->id,
                'attendance_log_id' => $logModel->id,
                'session_id' => $session->id,
                'out_time' => $outTime->toDateTimeString(),
            ]);
            $count++;
        }

        $this->info("Auto-checkout complete. Closed {$count} open session(s).");
    }
}
