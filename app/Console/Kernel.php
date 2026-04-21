<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Archive logs older than 30 days every month
        $schedule->command('logs:archive --days=30')->monthly();
        
        // Check for Statutory Compliance Due Dates
        $schedule->command('compliance:reminders')->dailyAt('09:00');
        $schedule->job(new \App\Jobs\ComplianceLicenceReminderJob)->dailyAt('09:00');
        
        // LMS: Mark overdue course assignments
        $schedule->call(function () {
            app(\App\Services\LMS\CourseAssignmentService::class)->markOverdue();
        })->daily()->at('00:01');
        
        // LMS: Send 3-day reminders
        $schedule->call(function () {
            app(\App\Services\LMS\CourseAssignmentService::class)->sendReminders(3);
        })->dailyAt('09:00');
        
        // LMS: Send 1-day reminders
        $schedule->call(function () {
            app(\App\Services\LMS\CourseAssignmentService::class)->sendReminders(1);
        })->dailyAt('09:00');

        // Attendance: Daily Processing (Mark Absent, Apply Policies)
        $schedule->command('attendance:process-daily')->dailyAt('01:00');
        $schedule->command('attendance:auto-checkout-open-sessions')->everyMinute()->withoutOverlapping();

        // Workflow: Process Timeouts
        $schedule->command('app:process-workflow-timeouts')->hourly();

        // Bug Tracker: SLA Reminders
        $schedule->job(new \App\Jobs\ProjectManagement\BugReminderJob)->hourly()->withoutOverlapping();
        
        // Bug Tracker: Auto-Close Stale Tickets
        $schedule->job(new \App\Jobs\ProjectManagement\BugAutoCloseJob)->dailyAt('02:00')->withoutOverlapping();

        // Visitor Hub: Check for overstayed visitors every 15 minutes
        $schedule->command('visitor:check-overstays')->everyFifteenMinutes();

        // High Availability Meetings
        $schedule->command('hrms:meeting-sync')->dailyAt('00:00');
        $schedule->job(new \App\Jobs\CRM\ReminderJob)->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
