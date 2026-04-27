<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        'is_electronic' => 'boolean',
        'custom_attributes' => 'array'
    ];
    


    public function assets() { return $this->hasMany(Asset::class, 'category_id'); }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }

    public function children() { return $this->hasMany(self::class, 'parent_id'); }

    public function tenant() { return $this->belongsTo(Tenant::class); }
    
    public function attributeDefinitions() { return $this->hasMany(AssetAttributeDefinition::class, 'asset_category_id'); }
}
