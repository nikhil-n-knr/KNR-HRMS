<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRegime extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function slabs()
    {
        return $this->hasMany(TaxSlab::class, 'regime_id')->orderBy('min_income', 'asc');
    }
}
