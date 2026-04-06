<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'ecosystem' => $this->loadEcosystem($request),
            'referrals' => $this->loadReferrals($request),
            default => $this->loadEcosystem($request),
        };
    }

    private function loadEcosystem(Request $request)
    {
        $tenantId = $this->tenantId;
        $partners = DB::table('crm_partners')->where('tenant_id', $tenantId)->get();

        return [
            'partners' => $partners,
            'stats' => [
                'active_partners' => $partners->where('status', 'active')->count(),
                'total_referrals' => DB::table('crm_referrals')->where('tenant_id', $tenantId)->count(),
                'avg_commission' => $partners->avg('commission_rate') ?? '12.5%'
            ]
        ];
    }

    private function loadReferrals(Request $request)
    {
        $tenantId = $this->tenantId;
        return [
            'referrals' => DB::table('crm_referrals')
                ->join('crm_partners', 'crm_referrals.partner_id', '=', 'crm_partners.id')
                ->where('crm_referrals.tenant_id', $tenantId)
                ->select('crm_referrals.*', 'crm_partners.name as partner_name')
                ->latest()
                ->get()
        ];
    }
}
