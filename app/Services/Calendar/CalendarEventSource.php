<?php

namespace App\Services\Calendar;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\User;

interface CalendarEventSource
{
    /**
     * @return Collection<CalendarEvent>
     */
    public function getEvents(Carbon $start, Carbon $end, User $user): Collection;
}
