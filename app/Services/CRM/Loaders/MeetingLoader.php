<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Meeting;
use App\Models\CRM\Contact;

class MeetingLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'calendar' => $this->loadCalendar(),
            'all_meetings' => $this->loadAllMeetings(),
            'analytics' => $this->loadAnalytics(),
            default => [],
        };
    }

    private function loadCalendar()
    {
        return [
            'initialEvents' => Meeting::where('tenant_id', $this->tenantId)
                ->with(['contacts:id,first_name,last_name', 'attendees.user:id,name', 'attendees.contact:id,first_name,last_name'])
                ->whereBetween('start_time', [now()->startOfMonth()->subWeek(), now()->endOfMonth()->addWeek()])
                ->get(),
            'contacts' => Contact::where('tenant_id', $this->tenantId)
                ->select('id', 'first_name', 'last_name', 'email')
                ->orderBy('first_name')
                ->get(),
        ];
    }

    private function loadAllMeetings()
    {
        return [
            'meetings' => Meeting::where('tenant_id', $this->tenantId)
                ->with(['contacts:id,first_name,last_name', 'attendees.user:id,name', 'attendees.contact:id,first_name,last_name'])
                ->latest('start_time')
                ->get(),
            'contacts' => Contact::where('tenant_id', $this->tenantId)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get(),
        ];
    }

    private function loadAnalytics()
    {
        return [
            'stats' => Meeting::where('tenant_id', $this->tenantId)
                ->selectRaw('count(*) as total, sum(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_duration')
                ->first(),
            'type_distribution' => Meeting::where('tenant_id', $this->tenantId)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get(),
        ];
    }
}
