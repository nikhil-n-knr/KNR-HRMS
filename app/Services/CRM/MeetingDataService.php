<?php

namespace App\Services\CRM;

use App\Models\Employee;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class MeetingDataService
{
    /**
     * Cache key for daily sync.
     */
    protected const CACHE_KEY = 'crm_meeting_data_sync';

    /**
     * Fetch active HRMS employees and CRM audience (contacts/leads).
     */
    public function getSyncData(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addDay(), function () {
            // 1. Fetch Employees with verified tokens
            $employees = Employee::whereNotNull('email')
                ->where('status', 'active')
                ->select(['id', 'first_name', 'last_name', 'email', 'zoom_token', 'google_token', 'ms_token'])
                ->get();

            // 2. Fetch CRM Audience
            $contacts = Contact::select(['id', 'first_name', 'last_name', 'email']);
            $leads = Lead::select(['id', 'first_name', 'last_name', 'email']);

            $audience = $contacts->union($leads)->get();

            return [
                'employees' => $employees,
                'audience' => $audience,
                'synced_at' => now(),
            ];
        });
    }

    /**
     * Force refresh the cache.
     */
    public function syncNow(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->getSyncData();
    }
}
