<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\WorkflowInitiated::class => [
            \App\Listeners\SendApprovalNotification::class,
        ],
        \App\Events\ApprovalActionTaken::class => [
            \App\Listeners\FinalizeWorkflow::class,
            \App\Listeners\SendApprovalNotification::class,
        ],
        \App\Events\ProjectManagement\TaskCompleted::class => [
            \App\Listeners\ProjectManagement\AwardTaskPoints::class,
        ],
        \App\Events\VisitorCheckedIn::class => [
            \App\Listeners\HandleVisitorCheckIn::class,
        ],
        \App\Events\DocumentActionEvent::class => [
            \App\Listeners\ProjectGovernanceListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register Observers
        \App\Models\BugTicket::observe(\App\Observers\BugTicketObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
