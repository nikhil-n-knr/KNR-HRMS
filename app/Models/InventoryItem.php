<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_recurring' => 'boolean',
        'recurring_config' => 'array'
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function category() { return $this->belongsTo(InventoryCategory::class, 'category_id'); }
    public function subcategory() { return $this->belongsTo(InventoryCategory::class, 'subcategory_id'); }
    public function defaultVendor() { return $this->belongsTo(Vendor::class, 'default_vendor_id'); }
    public function storageNode() { return $this->belongsTo(LocationNode::class, 'storage_node_id'); }
    public function transactions() { return $this->hasMany(InventoryTransaction::class, 'item_id'); }
    public function issueLines() { return $this->hasMany(InventoryIssueLine::class, 'item_id'); }
    public function recurringRules() { return $this->hasMany(RecurringConsumptionRule::class, 'item_id'); }
    public function clientSupplies() { return $this->hasMany(ClientItemSupply::class, 'item_id'); }
    public function locationAssignments() { return $this->morphMany(LocationAssignment::class, 'entity'); }

    // --- Attributes ---
    protected $appends = ['burn_rate', 'days_remaining'];

    public function getBurnRateAttribute()
    {
        // Calculate average daily consumption over last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        
        $totalConsumed = $this->transactions()
            ->where('type', 'OUT') // Assuming 'OUT' is consumption
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('quantity');

        // Avoid division by zero, min 1 day divisor if created recently? No, 30 days fixed window.
        // If consumption is 0, burn rate is 0.
        
        return round($totalConsumed / 30, 2);
    }

    public function getDaysRemainingAttribute()
    {
        $rate = $this->burn_rate;
        if ($rate <= 0) return 999; // Infinite / Safe

        return round($this->current_stock / $rate);
    }
}
