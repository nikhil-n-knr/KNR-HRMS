<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
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
    protected $description = 'Auto-checkout open sessions using shift-end + 1 hour caps';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info('Running auto-checkout scan at ' . $now->toDateTimeString());

        $registry = app(AttendanceRegistryService::class);
        $count = $registry->autoCheckoutOpenSessions($now);

        $this->info("Auto-checkout complete. Closed {$count} open session(s).");
    }
}
