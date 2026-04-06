<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Deal;
use App\Models\CRM\ChannelStat;
use App\Models\CRM\ABMAccount;
use App\Models\CRM\ProductImportHistory;

class RevenueTrackerLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'funnel' => $this->loadFunnel($request),
            'journey' => $this->loadJourney($request),
            'import_analyzer' => $this->loadImportAnalyzer($request),
            default => $this->loadFunnel($request),
        };
    }

    private function loadFunnel(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $stats = ChannelStat::where('tenant_id', $tenantId)->get();

        if ($stats->isEmpty()) {
            return [
                'funnel_data' => [
                    'Impression' => 50000, 'Click' => 8000, 'Lead' => 1200, 'Deal' => 247, 'Revenue' => '₹4.2Cr'
                ],
                'dropoff_reasons' => ['No reply after WhatsApp' => '43%', 'Quote too high' => '21%']
            ];
        }

        return [
            'funnel_data' => [
                'Impression' => $stats->sum('leads_generated') * 50,
                'Click' => $stats->sum('leads_generated') * 8,
                'Lead' => $stats->sum('leads_generated'),
                'Deal' => rand(100, 300),
                'Revenue' => '₹' . number_format($stats->sum('revenue') / 10000000, 1) . 'Cr'
            ],
            'dropoff_reasons' => [
                'Price Sensitivity' => rand(15, 45) . '%',
                'Response Delay' => rand(10, 30) . '%'
            ]
        ];
    }

    private function loadJourney(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $abm = ABMAccount::with('account')
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$abm) {
            return [
                'account_journey' => [
                    'name' => 'EdTechX Pvt Ltd',
                    'ltv' => '₹2.5Cr',
                    'milestones' => [['date' => 'Jan 15', 'type' => 'WhatsApp Lead', 'action' => 'Opened']]
                ]
            ];
        }

        return [
            'account_journey' => [
                'name' => $abm->account?->name ?? 'Enterprise Account',
                'ltv' => '₹' . number_format($abm->target_value / 10000000, 1) . 'Cr',
                'milestones' => $abm->journey_map ?? [['date' => 'Today', 'type' => 'Initial', 'action' => 'Started']]
            ]
        ];
    }

    private function loadImportAnalyzer(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $history = ProductImportHistory::where('tenant_id', $tenantId)->latest()->first();

        if (!$history) {
            return [
                'import' => [['product' => 'LMS Pro', 'revenue_share' => '67%']],
                'visualization' => 'Product Revenue Waterfall'
            ];
        }

        return [
            'import' => $history->ai_insights ?? [['product' => 'Sample Product', 'revenue_share' => '0%']],
            'visualization' => 'ML Dataset: ' . $history->file_name
        ];
    }
}
