<?php

namespace App\Services\Calendar;

use Carbon\Carbon;

class CalendarEvent
{
    public string $id;
    public string $title;
    public Carbon $start;
    public Carbon $end;
    public string $type; // 'interview', 'task', 'meeting', 'leave', 'shift'
    public string $color; // tailwind class or hex
    public bool $allDay;
    public array $metadata; // URL, description, status, etc.

    public function __construct(
        string $id,
        string $title,
        Carbon $start,
        Carbon $end,
        string $type,
        string $color = 'blue',
        bool $allDay = false,
        array $metadata = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
        $this->type = $type;
        $this->color = $color;
        $this->allDay = $allDay;
        $this->metadata = $metadata;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start->toIso8601String(),
            'end' => $this->end->toIso8601String(),
            'type' => $this->type,
            'color' => $this->color,
            'allDay' => $this->allDay,
            'extendedProps' => $this->metadata
        ];
    }
}
