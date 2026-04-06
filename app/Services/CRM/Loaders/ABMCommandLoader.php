<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\ABMAccount;
use App\Models\CRM\Account;

class ABMCommandLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'targets' => $this->loadTargets($request),
            'analytics' => $this->loadAnalytics($request),
            default => $this->loadTargets($request),
        };
    }

    private function loadTargets(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $abm = ABMAccount::with('account')
            ->where('tenant_id', $tenantId)
            ->get();

        if ($abm->isEmpty()) {
            return [
                'target_accounts' => [['company' => 'EdTechX', 'score' => 94, 'status' => 'Warm', 'next' => 'CEO Call', 'value' => '$250K']]
            ];
        }

        return [
            'target_accounts' => $abm->map(fn($a) => [
                'name' => $a->account?->name ?? 'Unknown',
                'score' => $a->abm_score,
                'status' => ucfirst($a->status),
                'next' => 'CEO OUTREACH',
                'value' => '₹' . number_format($a->target_value / 1000) . 'K'
            ])
        ];
    }

    private function loadAnalytics(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $abm = ABMAccount::where('tenant_id', $tenantId)->get();

        if ($abm->isEmpty()) {
            return [
                'abm_overview' => ['total targets' => 500, 'engaged' => 240, 'qualified' => 84],
                'journey_map' => ['Research', 'Personalized Outreach', 'AR Demo', 'Contract']
            ];
        }

        return [
            'abm_overview' => [
                'total targets' => $abm->count(),
                'engaged' => $abm->where('status', 'engaged')->count(),
                'qualified' => $abm->where('status', 'qualified')->count()
            ],
            'journey_map' => ['Research', 'Personalized Outreach', 'AR Demo', 'Contract']
        ];
    }
}
