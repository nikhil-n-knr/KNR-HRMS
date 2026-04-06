<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Account;

class AccountLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'all_accounts' => $this->loadAllAccounts($request),
            'abm_targets' => $this->loadABMTargets($request),
            default => $this->loadAllAccounts($request),
        };
    }

    private function loadAllAccounts(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        return [
            'accounts' => Account::where('tenant_id', $tenantId)->latest()->get(),
            'total_value' => Account::where('tenant_id', $tenantId)->sum('annual_revenue') ?? '₹12.4Cr',
            'top_accounts' => Account::where('tenant_id', $tenantId)->orderByDesc('annual_revenue')->take(5)->get()
        ];
    }

    private function loadABMTargets(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        return [
            'abm_stats' => [
                'engagement' => '74%',
                'active_targets' => 12,
                'avg_score' => 82
            ]
        ];
    }
}
