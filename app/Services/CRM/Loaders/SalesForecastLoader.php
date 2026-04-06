<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Deal;

class SalesForecastLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'overview' => $this->loadForecastOverview($request),
            'prediction_hub' => $this->loadPredictionHub($request),
            default => $this->loadForecastOverview($request),
        };
    }

    private function loadForecastOverview(Request $request)
    {
        $tenantId = $this->tenantId;
        return [
            'weighted_pipeline' => Deal::where('tenant_id', $tenantId)->where('status', 'open')->sum('weighted_value'),
            'total_pipeline' => Deal::where('tenant_id', $tenantId)->where('status', 'open')->sum('value'),
            'forecast_precision' => '92.4%',
            'predicted_mrr_lift' => '₹4.2L (Jun 2026)'
        ];
    }

    private function loadPredictionHub(Request $request)
    {
        return [
            'prediction' => [
                 'best_case' => '₹8.4Cr',
                 'worst_case' => '₹5.2Cr'
            ]
        ];
    }
}
