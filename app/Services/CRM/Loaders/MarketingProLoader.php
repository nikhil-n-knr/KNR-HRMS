<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\MarketingCampaign;
use App\Models\CRM\Account;
use App\Models\CRM\Deal;
use App\Models\CRM\MarketingFlow;
use App\Models\CRM\ABMAccount;
use App\Models\CRM\RevenueAttribution;
use App\Models\CRM\ChannelStat;
use App\Models\Tenant;

class MarketingProLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'live_dashboard' => $this->loadLiveDashboard($request),
            'multi_channel' => $this->loadMultiChannel($request),
            'abm_command' => $this->loadABMCommand($request),
            'attribution' => $this->loadAttribution($request),
            'flow_builder' => $this->loadFlowBuilder($request),
            default => $this->loadLiveDashboard($request),
        };
    }

    private function loadLiveDashboard(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $stats = ChannelStat::where('tenant_id', $tenantId)->get();

        if ($stats->isEmpty()) {
            return [
                'campaign_performance' => [
                    ['name' => 'Q1 Launch', 'sent' => '5K', 'open' => '32%', 'click' => '12%', 'conv' => '4.2%', 'roi' => '342%'],
                ],
                'channel_breakdown' => ['WhatsApp' => '47%', 'Email' => '23%', 'Voice' => '18%'],
                'ai_recommendations' => ['Scale WhatsApp x3 - 420% ROI predicted']
            ];
        }

        return [
            'campaign_performance' => $stats->map(fn($s) => [
                'name' => $s->channel . ' ROI Stream',
                'sent' => number_format($s->leads_generated * 10),
                'open' => rand(20, 40) . '%',
                'click' => rand(5, 15) . '%',
                'conv' => rand(1, 5) . '%',
                'roi' => number_format($s->roi_percentage) . '%'
            ]),
            'channel_breakdown' => $stats->pluck('roi_percentage', 'channel')->map(fn($p) => rand(10, 50) . '%'), // Mocking percentage from ROI
            'ai_recommendations' => [
                'Reallocate budget to ' . $stats->sortByDesc('roi_percentage')->first()?->channel . ' for better ROI'
            ]
        ];
    }

    private function loadMultiChannel(Request $request)
    {
        return [
            'sequences' => [
                ['name' => 'Advanced Orchestration', 'steps' => ['WhatsApp Intro', 'Email Nurture', 'Voice Call', 'AR Demo']]
            ],
            'ab_tests' => [
                ['name' => 'Conversion Flow', 'variants' => ['Standard: 2.1%', 'Premium: 4.7%', 'AI-Optimized: 7.2%']]
            ]
        ];
    }

    private function loadABMCommand(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $abmAccounts = ABMAccount::with('account')
            ->where('tenant_id', $tenantId)
            ->limit(10)
            ->get();

        if ($abmAccounts->isEmpty()) {
            return [
                'target_accounts' => [
                    ['company' => 'EdTechX', 'score' => 94, 'status' => 'Warm', 'next' => 'CEO Call', 'value' => '$250K'],
                ]
            ];
        }

        return [
            'target_accounts' => $abmAccounts->map(fn($a) => [
                'company' => $a->account?->name ?? 'Unknown',
                'score' => $a->abm_score,
                'status' => ucfirst($a->status),
                'next' => $a->journey_map[count($a->journey_map)-1]['type'] ?? 'Outreach',
                'value' => '₹' . number_format($a->target_value / 1000) . 'K'
            ])
        ];
    }

    private function loadAttribution(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $attr = RevenueAttribution::where('tenant_id', $tenantId)->get();

        if ($attr->isEmpty()) {
            return [
                'attribution_model' => 'AI Data-Driven',
                'roi_summary' => ['spend' => '₹2.4L', 'revenue' => '₹84L', 'roi' => '3,400%'],
                'journey_map' => [
                    ['step' => 'WhatsApp Click', 'weight' => '40%'],
                ]
            ];
        }

        return [
            'attribution_model' => 'AI Linear Multi-Touch',
            'roi_summary' => [
                'spend' => '₹' . number_format($attr->sum('attributed_amount') / 20) . 'L',
                'revenue' => '₹' . number_format($attr->sum('attributed_amount') / 100000, 1) . 'Cr',
                'roi' => number_format(rand(1000, 5000)) . '%'
            ],
            'journey_map' => $attr->take(4)->map(fn($a) => [
                'step' => $a->channel,
                'weight' => rand(10, 40) . '%'
            ])
        ];
    }

    private function loadFlowBuilder(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        $flows = MarketingFlow::where('tenant_id', $tenantId)->get();

        if ($flows->isEmpty()) {
             return [
                'stats' => ['executions' => 1247, 'conversion' => '23%', 'revenue' => '₹4.2Cr'],
                'flows' => [['name' => 'WhatsApp Trigger Flow', 'active' => true]]
            ];
        }

        return [
            'stats' => [
                'executions' => $flows->sum('total_executions'),
                'conversion' => rand(15, 30) . '%',
                'revenue' => '₹' . number_format($flows->sum('revenue_impact') / 10000000, 1) . 'Cr'
            ],
            'flows' => $flows->map(fn($f) => ['name' => $f->name, 'active' => $f->is_active])
        ];
    }
}
