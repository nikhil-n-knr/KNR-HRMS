<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientItemSupply extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'supplied_on' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
