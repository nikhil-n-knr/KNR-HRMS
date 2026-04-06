<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAttributeDefinition extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
}
