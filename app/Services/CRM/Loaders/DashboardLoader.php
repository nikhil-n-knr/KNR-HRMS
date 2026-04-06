<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Activity;
use App\Models\CRM\Lead;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\MarketingCampaign;
use App\Models\CRM\ContactSegment;
use App\Models\CRM\UserStat;
use App\Models\CRM\UserAchievement;

class DashboardLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'revenue_overview' => $this->loadRevenueOverview(),
            'customer_health' => $this->loadCustomerHealth(),
            'ai_insights' => $this->loadAIInsights(),
            'overview' => $this->loadOverview(),
            'sales_dash' => $this->loadSalesDash(),
            'marketing_dash' => $this->loadMarketingDash(),
            'support_dash' => $this->loadSupportDash(),
            'product_dash' => $this->loadProductDash(),
            'reports' => $this->loadReports(),
            default => $this->loadRevenueOverview(),
        };
    }

    private function loadRevenueOverview()
    {
        $tenantId = $this->tenantId;
        
        // Dynamic calculations
        $wonDeals = Deal::where('tenant_id', $tenantId)->where('status', 'won');
        $arr = $wonDeals->sum('value');
        $mrr = $arr / 12;
        
        $totalDealsCount = Deal::where('tenant_id', $tenantId)->count();
        $lostDealsCount = Deal::where('tenant_id', $tenantId)->where('status', 'lost')->count();
        $churnRate = $totalDealsCount > 0 ? ($lostDealsCount / $totalDealsCount) * 100 : 0;
        
        $pipelineValue = Deal::where('tenant_id', $tenantId)->where('status', 'open')->sum('value');
        $pipelineCoverage = $arr > 0 ? round($pipelineValue / ($arr / 4), 1) : 0;

        return [
            'metrics' => [
                'arr' => '₹' . number_format($arr / 10000000, 2) . 'Cr',
                'mrr' => '₹' . number_format($mrr / 100000, 2) . 'L',
                'churn' => number_format($churnRate, 1) . '%',
                'pipeline_coverage' => $pipelineCoverage . 'x'
            ],
            'leaks' => [
                ['title' => 'Expansion Opportunity', 'impact' => '₹12.4L'],
                ['title' => 'Unused Seat Leak', 'impact' => '₹4.2L'],
                ['title' => 'Price Plan Lag', 'impact' => '₹8.7L'],
            ],
            'forecast_data' => [40, 60, 45, 90, 65, 80, 100, 85, 95, 110, 130, 150]
        ];
    }

    private function loadCustomerHealth()
    {
        $accounts = \App\Models\CRM\ABMAccount::where('tenant_id', $this->tenantId)
            ->with('account')
            ->get()
            ->map(function($abm) {
                return [
                    'id' => $abm->id,
                    'name' => $abm->account?->name ?? 'Unknown',
                    'score' => $abm->abm_score,
                    'status' => $abm->status,
                ];
            });

        return [
            'accounts' => $accounts,
            'risks' => [
                ['name' => 'HealthScale Inc', 'reason' => 'Declining API Usage (22%)'],
                ['name' => 'FinLeap Solutions', 'reason' => 'Unresolved High Priority Tickets'],
                ['name' => 'EdTechX Hub', 'reason' => 'Overdue Subscription Invoice'],
            ],
            'playbooks' => [
                ['title' => 'At-Risk Alert Auto-Sync', 'subtitle' => 'Trigger for < 60 Health', 'icon' => 'fa-bell'],
                ['title' => 'Success Playbook v2', 'subtitle' => 'Onboarding & Renewal Path', 'icon' => 'fa-chess-king'],
                ['title' => 'Churn Prevention Flow', 'subtitle' => 'Retention Macro Sequences', 'icon' => 'fa-user-shield'],
            ]
        ];
    }

    private function loadAIInsights()
    {
        return [
            'insights' => [
                ['title' => '3 Deals at high risk of dropoff due to WhatsApp non-responsiveness', 'impact' => '₹12.4L', 'confidence' => 94, 'icon' => 'fa-comments-dollar'],
                ['title' => '2 Enterprise customers showing usage decline', 'impact' => '₹4.2Cr', 'confidence' => 88, 'icon' => 'fa-user-nurse'],
                ['title' => 'Upsell opportunity in Education Segment identified', 'impact' => '₹18.7L', 'confidence' => 76, 'icon' => 'fa-graduation-cap'],
            ],
            'actions' => [
                ['title' => 'Auto-schedule a Strategic QBR for at-risk enterprise accounts next Monday.'],
                ['title' => 'Deploy a personalized retention WhatsApp sequence for "LMS Pro" users.'],
                ['title' => 'Adjust pipeline forecast factor by -8% for mid-market deals.'],
            ],
            'ai_stats' => [
                ['label' => 'Predicted Yield', 'value' => '₹5.8Cr', 'icon' => 'fa-chart-area'],
                ['label' => 'Forecast Accuracy', 'value' => '92.4%', 'icon' => 'fa-bullseye'],
                ['label' => 'AI Optimization Lift', 'value' => '18.2%', 'icon' => 'fa-rocket'],
                ['label' => 'Total Sync Nodes', 'value' => '1,424', 'icon' => 'fa-network-wired'],
            ]
        ];
    }

    private function loadOverview()
    {
        $totalLeads = Lead::where('tenant_id', $this->tenantId)->count();
        $convertedLeads = Lead::where('tenant_id', $this->tenantId)->whereNotNull('converted_to_contact_id')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        $closedDeals = Deal::where('tenant_id', $this->tenantId)->whereIn('status', ['won', 'lost'])->count();
        $wonDeals = Deal::where('tenant_id', $this->tenantId)->where('status', 'won')->count();
        $winRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 1) : 0;

        return [
            'activities' => Activity::where('tenant_id', $this->tenantId)
                ->with(['activityable', 'creator', 'assignee'])
                ->latest()
                ->take(20)
                ->get(),
            'metrics' => [
                'total_leads' => $totalLeads,
                'total_contacts' => Contact::where('tenant_id', $this->tenantId)->count(),
                'open_deals' => Deal::where('tenant_id', $this->tenantId)->where('status', 'open')->count(),
                'revenue_mtd' => Deal::where('tenant_id', $this->tenantId)->where('status', 'won')->whereMonth('updated_at', now()->month)->sum('value'),
                'pipeline_value' => Deal::where('tenant_id', $this->tenantId)->where('status', 'open')->sum('value'),
                'weighted_pipeline' => Deal::where('tenant_id', $this->tenantId)->where('status', 'open')->sum('weighted_value'),
                'avg_health' => Deal::where('tenant_id', $this->tenantId)->where('status', 'open')->avg('health_score') ?? 0,
                'conversion_rate' => $conversionRate,
                'win_rate' => $winRate,
                'avg_deal_size' => Deal::where('tenant_id', $this->tenantId)->where('status', 'won')->avg('value') ?? 0,
                'todays_meetings' => \App\Models\CRM\Meeting::where('tenant_id', $this->tenantId)
                    ->whereDate('start_time', today())
                    ->with('contacts')
                    ->orderBy('start_time')
                    ->get(),
                'leaderboard' => UserStat::where('tenant_id', $this->tenantId)
                    ->with('user:id,name')
                    ->orderByDesc('total_points')
                    ->take(5)
                    ->get(),
                'user_achievements' => UserAchievement::where('user_id', auth()->id())
                    ->with('achievement')
                    ->latest()
                    ->take(3)
                    ->get()
            ]
        ];
    }

    private function loadSalesDash()
    {
        return [
            'pipeline' => Deal::where('tenant_id', $this->tenantId)
                ->selectRaw('stage, count(*) as count, sum(value) as total_value, sum(weighted_value) as weighted_value')
                ->groupBy('stage')
                ->get(),
            'health_distribution' => [
                'good' => Deal::where('tenant_id', $this->tenantId)->where('status', 'open')->where('health_score', '>=', 80)->count(),
                'average' => Deal::where('tenant_id', $this->tenantId)->where('status', 'open')->whereBetween('health_score', [50, 79])->count(),
                'at_risk' => Deal::where('tenant_id', $this->tenantId)->where('status', 'open')->where('health_score', '<', 50)->count(),
            ],
            'top_deals' => Deal::where('tenant_id', $this->tenantId)
                ->where('status', 'open')
                ->with(['account', 'assignee'])
                ->orderByDesc('value')
                ->take(5)
                ->get(),
            'sales_rep_performance' => \App\Models\User::where('tenant_id', $this->tenantId)
                ->whereHas('assignedDeals')
                ->withCount(['assignedDeals as deals_count', 'assignedDeals as won_deals' => function($q) {
                    $q->where('status', 'won');
                }])
                ->get(),
        ];
    }

    private function loadMarketingDash()
    {
        return [
            'campaign_stats' => MarketingCampaign::where('tenant_id', $this->tenantId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'avg_success_score' => MarketingCampaign::where('tenant_id', $this->tenantId)
                ->where('status', 'active')
                ->avg('predicted_success_score') ?? 0,
            'recent_campaigns' => MarketingCampaign::where('tenant_id', $this->tenantId)
                ->with(['template'])
                ->latest()
                ->take(5)
                ->get(),
            'segment_distribution' => ContactSegment::where('tenant_id', $this->tenantId)
                ->withCount('contacts')
                ->get(),
        ];
    }

    private function loadSupportDash()
    {
        return [
            'ticket_stats' => \App\Models\CRM\Ticket::where('tenant_id', $this->tenantId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'sla_stats' => [
                'avg_resolution_time' => \App\Models\CRM\Ticket::where('tenant_id', $this->tenantId)
                    ->where('status', 'resolved')
                    ->whereNotNull('resolved_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                    ->first()->avg_hours ?? 0,
                'urgent_tickets' => \App\Models\CRM\Ticket::where('tenant_id', $this->tenantId)
                    ->where('priority', 'urgent')
                    ->where('status', '!=', 'resolved')
                    ->count()
            ],
            'recent_tickets' => \App\Models\CRM\Ticket::where('tenant_id', $this->tenantId)
                ->with(['contact', 'assignee'])
                ->latest()
                ->take(10)
                ->get(),
        ];
    }

    private function loadProductDash()
    {
        return [
            'top_selling' => \App\Models\CRM\ProductSalesHistory::where('tenant_id', $this->tenantId)
                ->with('product:id,name')
                ->orderByDesc('units_sold')
                ->take(5)
                ->get(),
            'profit_heatmap' => \App\Models\CRM\Product::where('tenant_id', $this->tenantId)
                ->with('category:id,name')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category?->name,
                    'margin' => $p->profit_margin
                ]),
            'products' => \App\Models\CRM\Product::where('tenant_id', $this->tenantId)->get(['id', 'name', 'type']),
        ];
    }

    private function loadReports()
    {
        return [
            'monthly_revenue' => Deal::where('tenant_id', $this->tenantId)
                ->where('status', 'won')
                ->selectRaw('MONTH(updated_at) as month, sum(value) as revenue')
                ->whereYear('updated_at', now()->year)
                ->groupBy('month')
                ->get(),
            'lead_sources' => Lead::where('tenant_id', $this->tenantId)
                ->selectRaw('source, count(*) as count')
                ->groupBy('source')
                ->get(),
        ];
    }
}
