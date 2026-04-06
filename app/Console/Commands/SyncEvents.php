<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EventService;

class SyncEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitors:sync-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate event state transitions and bulk check-outs for completed events';

    /**
     * Execute the console command.
     */
    public function handle(EventService $eventService)
    {
        $this->info('Starting Event Lifecycle Synchronization...');

        // 1. Update states (Scheduled -> Live -> Completed)
        $eventService->updateEventStates();
        $this->info('Event states updated.');

        // 2. Perform automated check-outs for completed events (Wrap-up)
        // This ensures no visitors are left "Checked-In" after an event ends.
        $count = $eventService->autoCleanupCompletedEvents();
        $this->info("Automated cleanup performed for {$count} events.");

        $this->info('Synchronization Complete.');
    }
}
