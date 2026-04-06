<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Lead;
use App\Models\CRM\Account;
use App\Models\User;

class LeadLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'all_leads' => $this->loadAllLeads($request),
            'scoring' => $this->loadScoring(),
            'assignment' => $this->loadAssignment(),
            'conversion' => $this->loadConversion(),
            default => [],
        };
    }

    private function loadAllLeads(Request $request)
    {
        $query = Lead::where('tenant_id', $this->tenantId);
        
        if ($request->get('view') === 'my') {
            $query->where('assigned_to', auth()->id());
        }

        return [
            'leads' => $query->with(['creator', 'convertedToAccount'])
                ->latest()
                ->get(),
            'users' => User::where('tenant_id', $this->tenantId)->get(['id', 'name']),
            'accounts' => Account::where('tenant_id', $this->tenantId)->get(['id', 'name']),
            'sources' => ['website', 'referral', 'social_media', 'cold_call', 'event', 'other'],
            'statuses' => ['new', 'contacted', 'qualified', 'unqualified', 'converted'],
        ];
    }

    private function loadScoring()
    {
        return [
            'rules' => \App\Models\CRM\LeadScoreRule::where('tenant_id', $this->tenantId)->get(),
        ];
    }

    private function loadAssignment()
    {
        return [
            'rules' => \App\Models\CRM\LeadAssignmentRule::where('tenant_id', $this->tenantId)->get(),
            'users' => User::where('tenant_id', $this->tenantId)->get(['id', 'name']),
        ];
    }

    private function loadConversion()
    {
        return [
            'conversion_stats' => Lead::where('tenant_id', $this->tenantId)
                ->selectRaw('source, count(*) as total, count(converted_at) as converted')
                ->groupBy('source')
                ->get()
                ->map(fn($row) => [
                    'source' => ucfirst($row->source),
                    'total' => $row->total,
                    'converted' => $row->converted,
                    'rate' => $row->total > 0 ? round(($row->converted / $row->total) * 100, 1) : 0
                ]),
        ];
    }
}
