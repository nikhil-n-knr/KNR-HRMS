<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_electronic' => 'boolean',
        'custom_attributes' => 'array'
    ];
    


    public function assets() { return $this->hasMany(Asset::class, 'category_id'); }
    
    public function attributeDefinitions() { return $this->hasMany(AssetAttributeDefinition::class, 'asset_category_id'); }
}
