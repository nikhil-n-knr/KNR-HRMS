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

class MeetingInviteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $meeting;
    public $isUpdate;
    public $isCancelled;

    /**
     * Create a new job instance.
     */
    public function __construct(Meeting $meeting, $isUpdate = false, $isCancelled = false)
    {
        $this->meeting = $meeting;
        $this->isUpdate = $isUpdate;
        $this->isCancelled = $isCancelled;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->meeting->load(['attendees.user', 'attendees.contact', 'attendees.lead', 'senderAccount']);

        $fromAddress = $this->meeting->senderAccount ? $this->meeting->senderAccount->email_address : config('mail.from.address');
        $fromName = auth()->check() ? auth()->user()->name : config('mail.from.name');

        foreach ($this->meeting->attendees as $attendee) {
            $email = null;

            if ($attendee->user) {
                $email = $attendee->user->email;
            } elseif ($attendee->contact) {
                $email = $attendee->contact->email;
            } elseif ($attendee->lead) {
                $email = $attendee->lead->email;
            } elseif ($attendee->email) {
                $email = $attendee->email;
            }

            if ($email) {
                Mail::to($email)
                    ->send(new MeetingInvitation(
                        $this->meeting, 
                        $this->isUpdate, 
                        $this->isCancelled, 
                        $fromAddress, 
                        $fromName
                    ));
            }
        }
    }
}
