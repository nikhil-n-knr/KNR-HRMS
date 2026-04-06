<?php

namespace App\Jobs;

use App\Models\CRM\Meeting;
use App\Mail\MeetingInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MeetingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     * This job should be scheduled to run every minute: $schedule->job(new MeetingReminderJob)->everyMinute();
     */
    public function handle(): void
    {
        $now = Carbon::now();
        
        // Find all scheduled meetings
        $meetings = Meeting::where('status', 'scheduled')
            ->whereNotNull('reminders_config')
            ->get();

        foreach ($meetings as $meeting) {
            $reminders = $meeting->reminders_config;
            if (!is_array($reminders)) continue;

            foreach ($reminders as $reminder) {
                $offset = $reminder['offset'] ?? 0; // minutes
                $reminderTime = Carbon::parse($meeting->start_time)->subMinutes($offset);

                // If currently within the minute of the reminder time
                if ($now->isSameMinute($reminderTime)) {
                    $this->dispatchReminder($meeting);
                }
            }
        }
    }

    private function dispatchReminder(Meeting $meeting)
    {
        $meeting->load('contacts');
        foreach ($meeting->contacts as $contact) {
            if ($contact->email) {
                Mail::to($contact->email)->send(new MeetingInvitation($meeting, true)); // treated as an update/reminder
            }
        }
    }
}
