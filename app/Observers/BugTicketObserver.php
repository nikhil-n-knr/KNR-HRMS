<?php

namespace App\Observers;

use App\Models\BugTicket;

class BugTicketObserver
{
    /**
     * Set SLA due date and check if breached.
     */
    private function calculateSla(BugTicket $bugTicket): void
    {
        // Only set sla_due_at if it's not set
        if (!$bugTicket->sla_due_at) {
            $hours = match ($bugTicket->severity) {
                'critical' => 4,
                'high' => 24,
                'medium' => 72,
                'low' => 120, // 5 days
                default => 72,
            };
            
            $bugTicket->sla_due_at = now()->addHours($hours);
        }

        // Check if breached
        if ($bugTicket->sla_due_at && now()->isAfter($bugTicket->sla_due_at)) {
            // Check if final stage to freeze the SLA breach status
            $isFinalStage = $bugTicket->stage && $bugTicket->stage->is_final;
            if (!$isFinalStage) {
                $bugTicket->is_sla_breached = true;
            }
        }
    }

    /**
     * Handle the BugTicket "creating" event.
     */
    public function creating(BugTicket $bugTicket): void
    {
        $this->calculateSla($bugTicket);
    }

    /**
     * Handle the BugTicket "updating" event.
     */
    public function updating(BugTicket $bugTicket): void
    {
        $this->calculateSla($bugTicket);
    }

    /**
     * Handle the BugTicket "created" event.
     */
    public function created(BugTicket $bugTicket): void
    {
        //
    }

    /**
     * Handle the BugTicket "updated" event.
     */
    public function updated(BugTicket $bugTicket): void
    {
        //
    }

    /**
     * Handle the BugTicket "deleted" event.
     */
    public function deleted(BugTicket $bugTicket): void
    {
        //
    }

    /**
     * Handle the BugTicket "restored" event.
     */
    public function restored(BugTicket $bugTicket): void
    {
        //
    }

    /**
     * Handle the BugTicket "force deleted" event.
     */
    public function forceDeleted(BugTicket $bugTicket): void
    {
        //
    }
}
