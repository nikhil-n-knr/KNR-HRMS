<?php

namespace App\Jobs;

use App\Models\VisitorPass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendHostNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $visitorPass;

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
        // In a real application, you would send an email, push notification, or Slack message here.
        // For demonstration, we simply log the event.
        $host = $this->visitorPass->visitor->host;
        $visitor = $this->visitorPass->visitor;

        if ($host) {
            Log::info("Notification sent to host {$host->name}: Your visitor {$visitor->name} has arrived at the front desk.");
        }
    }
}
