<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CalculateAssetDepreciation extends Command
{
    protected $signature = 'assets:calculate-depreciation';
    protected $description = 'Calculate daily depreciation for all assets based on Straight Line method';

    public function handle()
    {
        $this->info('Starting depreciation calculation...');
        
        $assets = \App\Models\Asset::whereNotNull('purchase_cost')
            ->whereNotNull('purchase_date')
            ->where('current_value', '>', 0) // Stop if already fully depreciated
            ->get();

        $updated = 0;

        foreach ($assets as $asset) {
            // Default 5 years useful life (approx 1825 days)
            $usefulLifeDays = 5 * 365; 
            
            // Daily Depreciation = Cost / Useful Life
            $dailyDepreciation = $asset->purchase_cost / $usefulLifeDays;
            
            // Days since purchase
            $daysOld = \Carbon\Carbon::parse($asset->purchase_date)->diffInDays(now());
            
            // Calculate Current Value
            $newValue = max(0, $asset->purchase_cost - ($dailyDepreciation * $daysOld));
            
            $asset->update(['current_value' => round($newValue, 2)]);
            $updated++;
        }

        $this->info("Depreciation calculated for {$updated} assets.");
    }
}
