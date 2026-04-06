<?php

namespace App\Jobs;

use App\Models\VisitorPass;
use App\Mail\VisitorInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEventInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visitorPass;

    /**
     * Create a new job instance.
     */
    public function __construct(VisitorPass $visitorPass)
    {
        $this->visitorPass = $visitorPass;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->visitorPass->visitor->email)->send(
            new VisitorInvitation($this->visitorPass)
        );
    }
}
