<?php

namespace App\Services;

use App\Models\Event;
use App\Models\VisitorPass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventService
{
    /**
     * Updates event status based on current time and capacity.
     */
    public function updateEventStates()
    {
        $now = now();

        // Mark as Active/Checking-In
        Event::where('status', 'Scheduled')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->update(['status' => 'Live']);

        // Mark as Completed
        Event::whereIn('status', ['Scheduled', 'Live'])
            ->where('end_time', '<=', $now)
            ->update(['status' => 'Completed']);
    }

    /**
     * Recalculates guest capacity for an event.
     */
    public function getCapacityStats(Event $event)
    {
        return [
            'limit' => $event->guest_limit,
            'confirmed' => $event->passes()->where('status', 'Pre-Registered')->count(),
            'checked_in' => $event->passes()->where('status', 'Checked-In')->count(),
            'available' => $event->guest_limit - $event->passes()->count(),
        ];
    }

    /**
     * Dispatches bulk actions for guests.
     */
    public function bulkAction(array $passIds, string $action)
    {
        $passes = VisitorPass::whereIn('id', $passIds)->get();

        foreach ($passes as $pass) {
            switch ($action) {
                case 'resend_invite':
                    // Logic to dispatch mailable
                    break;
                case 'revoke':
                    $pass->update(['status' => 'Revoked']);
                    break;
                case 'upgrade_vip':
                    $pass->visitor->update(['is_vip' => true]);
                    break;
            }
        }

        return true;
    }

    /**
     * Automatically checks out all visitors from completed events.
     */
    public function autoCleanupCompletedEvents()
    {
        $completedEvents = Event::where('status', 'Completed')
            ->whereHas('passes', function($query) {
                $query->where('status', 'Checked-In');
            })->get();

        foreach ($completedEvents as $event) {
            $this->bulkCheckout($event);
        }

        return $completedEvents->count();
    }

    /**
     * Checks out all guests for a specific event.
     */
    public function bulkCheckout(Event $event)
    {
        $event->passes()->where('status', 'Checked-In')->update([
            'status' => 'Checked-Out',
            'check_out_at' => now(),
        ]);

        return true;
    }

    /**
     * Generates a performance report for an event.
     */
    public function generatePerformanceReport(Event $event)
    {
        $totalInvited = $event->passes()->count();
        $totalArrived = $event->passes()->whereNotNull('check_in_at')->count();
        
        $arrivals = $event->passes()
            ->whereNotNull('check_in_at')
            ->select(DB::raw('HOUR(check_in_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        return [
            'total_invited' => $totalInvited,
            'total_arrived' => $totalArrived,
            'no_show_rate' => $totalInvited > 0 ? round((($totalInvited - $totalArrived) / $totalInvited) * 100, 2) : 0,
            'peak_arrival_hour' => !empty($arrivals) ? (array_search(max($arrivals), $arrivals)) : null,
            'arrival_density' => $arrivals,
        ];
    }
}
