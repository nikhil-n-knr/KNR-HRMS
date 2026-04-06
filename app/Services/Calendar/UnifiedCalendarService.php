<?php

namespace App\Services\Calendar;

use App\Services\Calendar\Sources\InterviewEventSource;
use App\Services\Calendar\Sources\TaskEventSource;
use App\Services\Calendar\Sources\LeaveEventSource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UnifiedCalendarService
{
    protected array $sources = [
        InterviewEventSource::class,
        TaskEventSource::class,
        LeaveEventSource::class,
        // Add ShiftEventSource later
    ];

    public function fetchEvents(User $user, Carbon $start, Carbon $end): Collection
    {
        $events = collect();

        foreach ($this->sources as $sourceClass) {
            try {
                $source = app($sourceClass);
                if ($source instanceof CalendarEventSource) {
                    $sourceEvents = $source->getEvents($start, $end, $user);
                    $events = $events->merge($sourceEvents);
                }
            } catch (\Exception $e) {
                Log::error("Failed to fetch calendar events from source {$sourceClass}: " . $e->getMessage());
                // Continue to next source to prevent partial failure from breaking the whole calendar
            }
        }

        return $events;
    }
}
