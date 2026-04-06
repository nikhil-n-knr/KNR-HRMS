<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAttributeValue extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function definition()
    {
        return $this->belongsTo(AssetAttributeDefinition::class, 'attribute_definition_id');
    }
}
