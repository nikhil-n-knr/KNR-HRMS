<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\SalesForecast; // Updated namespace
use App\Models\CRM\Deal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SalesForecastController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $period = $request->input('period', 'quarter'); // month, quarter, year

        // 1. Calculate weighted pipeline for the current period
        $pipeline = Deal::where('tenant_id', $tenantId)
            ->whereNull('closed_at') // Open deals
            ->get()
            ->map(function ($deal) {
                // Assuming we have stage probability mapping, for now simplify
                $probability = $this->getProbability($deal->stage_id);
                return [
                    'value' => $deal->value,
                    'weighted_value' => $deal->value * ($probability / 100),
                    'stage' => $deal->stage_id // Or relationship
                ];
            });

        $forecastTotal = $pipeline->sum('weighted_value');
        $pipelineTotal = $pipeline->sum('value');

        // 2. Get User quotas vs actuals
        // This would ideally come from a Quota model, using static for now
        $quota = 150000; 
        $closedWon = Deal::where('tenant_id', $tenantId)
             ->whereNotNull('won_at')
             ->sum('value');

        return Inertia::render('CRM/Sales/Forecasting', [
            'forecast' => [
                'total_forecast' => $forecastTotal,
                'pipeline_coverage' => $pipelineTotal,
                'quota' => $quota,
                'attainment' => $quota > 0 ? ($closedWon / $quota) * 100 : 0,
                'closed_won_period' => $closedWon
            ],
            // Return chart data
        ]);
    }

    private function getProbability($stageId) {
        // Mock probability map - replace with DB lookup later
        $map = [
            1 => 10,  // Discovery
            2 => 30,  // Proposal
            3 => 60,  // Negotiation
            4 => 100, // Won
            5 => 0    // Lost
        ];
        return $map[$stageId] ?? 20;
    }
}
