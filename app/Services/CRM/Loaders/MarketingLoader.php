<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\MarketingCampaign;
use App\Models\CRM\MarketingTemplate;
use App\Models\CRM\ContactSegment;
use App\Models\CRM\MarketingAutomation;
use App\Models\CRM\CampaignEvent;

class MarketingLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'campaigns' => $this->loadCampaigns(),
            'templates' => $this->loadTemplates(),
            'analytics' => $this->loadAnalytics(),
            'nurturing' => $this->loadNurturing(),
            'promotions' => $this->loadPromotions(),
            default => [],
        };
    }

    private function loadPromotions()
    {
        return [
            'promotions' => MarketingCampaign::where('tenant_id', $this->tenantId)
                ->where('type', 'promotion')
                ->latest()
                ->get(),
            'products' => \App\Models\CRM\Product::where('tenant_id', $this->tenantId)
                ->where('active', true)
                ->get(),
        ];
    }

    private function loadCampaigns()
    {
        return [
            'campaigns' => MarketingCampaign::where('tenant_id', $this->tenantId)
                ->with(['template', 'segment'])
                ->latest()
                ->get(),
            'templates' => MarketingTemplate::where('tenant_id', $this->tenantId)->get(),
            'segments' => ContactSegment::where('tenant_id', $this->tenantId)->withCount('contacts')->get(),
        ];
    }

    private function loadTemplates()
    {
        return [
            'templates' => MarketingTemplate::where('tenant_id', $this->tenantId)
                ->latest()
                ->get(),
        ];
    }

    private function loadAnalytics()
    {
        return [
            'campaign_events' => CampaignEvent::whereHas('campaign', function($q) {
                $q->where('tenant_id', $this->tenantId);
            })->with(['campaign', 'contact'])->latest()->take(100)->get(),
            
            'aggregate_stats' => MarketingCampaign::where('tenant_id', $this->tenantId)
                ->selectRaw('SUM(JSON_EXTRACT(stats, "$.total")) as total, SUM(JSON_EXTRACT(stats, "$.opened")) as opened, SUM(JSON_EXTRACT(stats, "$.clicked")) as clicked')
                ->first(),
        ];
    }

    private function loadNurturing()
    {
        return [
            'automations' => MarketingAutomation::where('tenant_id', $this->tenantId)
                ->withCount('steps')
                ->latest()
                ->get(),
        ];
    }
}
