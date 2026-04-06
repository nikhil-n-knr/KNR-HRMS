<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Deal;
use App\Models\CRM\PipelineStage;
use App\Models\CRM\Account;
use App\Models\CRM\Product;

class SalesLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'kanban', 'all_deals' => $this->loadDealsGrid($request),
            'forecasting' => $this->loadForecasting($request),
            'quotes' => $this->loadQuotes($request),
            'won' => $this->loadDealsByStatus('won', $request),
            'lost' => $this->loadDealsByStatus('lost', $request),
            default => [],
        };
    }

    private function loadDealsGrid(Request $request)
    {
        $query = Deal::where('tenant_id', $this->tenantId);
        
        if ($request->get('view') === 'my') {
            $query->where('assigned_to', auth()->id());
        }

        return [
            'deals' => $query->with(['account', 'contact', 'assignee'])
                ->latest()
                ->get(),
            'stages' => PipelineStage::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->orderBy('order')
                ->get(),
            'accounts' => Account::where('tenant_id', $this->tenantId)->get(['id', 'name']),
        ];
    }

    private function loadForecasting(Request $request)
    {
        $stages = PipelineStage::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->get();

        $query = Deal::where('tenant_id', $this->tenantId)->where('status', 'open');
        if ($request->get('view') === 'my') {
            $query->where('assigned_to', auth()->id());
        }

        return [
            'forecast' => $query->selectRaw('stage, sum(value) as total_value')
                ->groupBy('stage')
                ->get()
                ->map(function($row) use ($stages) {
                    $stage = $stages->where('name', $row->stage)->first();
                    $prob = $stage ? $stage->win_probability / 100 : 0.1;
                    return [
                        'stage' => ucfirst($row->stage),
                        'total_value' => $row->total_value,
                        'weighted_value' => $row->total_value * $prob
                    ];
                }),
        ];
    }

    private function loadQuotes(Request $request)
    {
        $tenantId = $this->tenantId;
        return [
            'quotes' => class_exists(\App\Models\CRM\Quote::class) 
                ? \App\Models\CRM\Quote::where('tenant_id', $tenantId)->with(['account', 'contact'])->get() 
                : [],
            'products' => Product::where('tenant_id', $tenantId)
                ->select('id', 'name', 'base_price', 'description')
                ->get(),
            'accounts' => Account::where('tenant_id', $tenantId)->select('id', 'name')->get(),
            'contacts' => \App\Models\CRM\Contact::where('tenant_id', $tenantId)->select('id', 'first_name', 'last_name')->get(),
        ];
    }

    private function loadDealsByStatus(string $status, Request $request)
    {
        $query = Deal::where('tenant_id', $this->tenantId)
            ->where('status', $status);

        if ($request->get('view') === 'my') {
            $query->where('assigned_to', auth()->id());
        }

        return [
            'deals' => $query->with(['account', 'contact', 'assignee'])
                ->latest()
                ->get(),
        ];
    }
}
