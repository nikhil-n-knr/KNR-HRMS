<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringConsumptionRule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'auto_create_request' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function scope()
    {
        return $this->morphTo();
    }
}
