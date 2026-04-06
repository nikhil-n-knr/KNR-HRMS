<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitItem extends Model
{
    protected $guarded = [];

    public function kit()
    {
        return $this->belongsTo(Kit::class);
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
}
