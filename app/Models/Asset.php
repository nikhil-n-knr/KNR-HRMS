<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'meta' => 'array',
        'is_electronic' => 'boolean',
        'is_serialized' => 'boolean'
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function category() { return $this->belongsTo(AssetCategory::class); }
    public function vendor() { return $this->belongsTo(Vendor::class); }
    public function assignment() { return $this->hasOne(AssetAssignment::class)->latestOfMany(); }
    public function assignments() { return $this->hasMany(AssetAssignment::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function maintenanceLogs() { return $this->hasMany(AssetMaintenanceLog::class); }

    /**
     * TCO (Total Cost of Ownership) Analysis
     */
    protected $appends = ['tco_analysis'];

    public function getTcoAnalysisAttribute()
    {
         // 1. Calculate Age in Days
         $purchaseDate = $this->purchase_date ?? now();
         $ageInDays = $purchaseDate->diffInDays(now()) ?: 1; // Avoid divide by zero

         // 2. Sum Maintenance Costs
         $totalMaintenance = $this->maintenanceLogs->sum('cost');

         // 3. Total Cost
         $totalCost = ($this->purchase_cost ?? 0) + $totalMaintenance;

         // 4. Cost Per Day
         $costPerDay = round($totalCost / $ageInDays, 2);

         // 5. Intelligent Recommendation
         $recommendation = 'Keep';
         $healthScore = 100;

         // Logic: If maintenance cost is > 50% of original price, considering scrapping
         if ($this->purchase_cost > 0 && $totalMaintenance > ($this->purchase_cost * 0.5)) {
             $recommendation = 'Scrap (High Maintainence)';
             $healthScore = 40;
         }

         // Logic: If older than 5 years (approx) for electronics
         if ($this->is_electronic && $ageInDays > (365 * 5)) {
             $recommendation = 'Refresh (End of Life)';
             $healthScore = 20;
         }

         return [
             'total_maintenance' => $totalMaintenance,
             'total_tco' => $totalCost,
             'cost_per_day' => $costPerDay,
             'age_days' => $ageInDays,
             'recommendation' => $recommendation,
             'health_score' => $healthScore
         ];
    }
}
