<?php

namespace App\Listeners;

use App\Events\VisitorCheckedIn;
use App\Jobs\SendHostNotificationJob;
use App\Jobs\GenerateBadgeJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleVisitorCheckIn
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VisitorCheckedIn $event): void
    {
        // 1. Send Host Notification
        if ($event->visitorPass->visitor->host_id) {
            SendHostNotificationJob::dispatch($event->visitorPass);
        }

        // 2. Generate and Spool Badge (If allowed by workflow)
        $metaData = $event->visitorPass->meta_data ?? [];
        if (($metaData['print_badge'] ?? true) === true) {
            GenerateBadgeJob::dispatch($event->visitorPass);
        }

        // 3. (Optional) Sync with Active Directory if needed...
    }
}
