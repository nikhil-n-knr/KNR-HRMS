<?php

namespace App\Services\Analytics;

use App\Models\Asset;
use Carbon\Carbon;

class PredictiveService
{
    /**
     * Forecast maintenance relative to useful life.
     * Heuristic: 
     * - Laptops: Maintenance every 6 months.
     * - Vehicles: Maintenance every 3 months.
     * - Others: Yearly.
     */
    public function getMaintenanceForecast()
    {
        // Get active assets
        $assets = Asset::whereIn('status', ['In_Service', 'Assigned', 'Available'])
            ->with('category', 'maintenanceLogs')
            ->get();

        $forecast = $assets->map(function ($asset) {
            $lastDiff = $asset->maintenanceLogs->max('service_date');
            $lastService = $lastDiff ? Carbon::parse($lastDiff) : Carbon::parse($asset->purchase_date);
            
            // Rules
            $interval = 12; // Months
            if (str_contains(strtolower($asset->category->name ?? ''), 'laptop')) $interval = 6;
            if (str_contains(strtolower($asset->category->name ?? ''), 'vehicle')) $interval = 3;

            $nextService = $lastService->copy()->addMonths($interval);
            $riskLevel = 'Low';

            $daysUntil = now()->diffInDays($nextService, false); // negative means overdue

            if ($daysUntil < 0) $riskLevel = 'Critical';
            elseif ($daysUntil < 30) $riskLevel = 'High';
            elseif ($daysUntil < 90) $riskLevel = 'Medium';

            return [
                'asset_id' => $asset->id,
                'name' => $asset->name,
                'category' => $asset->category->name ?? 'Unknown',
                'last_service' => $lastService->format('Y-m-d'),
                'next_service_due' => $nextService->format('Y-m-d'),
                'days_until' => round($daysUntil),
                'risk_level' => $riskLevel
            ];
        });

        // Return only upcoming or overdue
        return $forecast->filter(fn($item) => $item['days_until'] < 90)->values();
    }
}
