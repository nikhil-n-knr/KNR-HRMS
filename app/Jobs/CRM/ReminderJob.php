<?php

namespace App\Jobs\CRM;

use App\Models\CRM\Meeting;
use App\Mail\CRM\MeetingInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $meetings = Meeting::where('start_time', '>', now())
            ->where('start_time', '<=', now()->addMinutes(15))
            ->where('status', Meeting::STATUS_PENDING) // Scheduled/active
            ->get();

        foreach ($meetings as $m) {
            foreach ($m->attendees as $a) {
                // Skip if they declined
                if ($a->status === Meeting::STATUS_DECLINED) continue;

                if ($a->status === 'declined') continue;
                
                if ($a->email) {
                    Mail::to($a->email)->queue(new MeetingInvitation($m, $m->link));
                }
            }
        }
    }
}
