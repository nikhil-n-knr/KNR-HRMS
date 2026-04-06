<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'regime_id', 
        'min_income', 
        'max_income', 
        'tax_rate_percentage'
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'tax_rate_percentage' => 'decimal:2',
    ];

    public function regime()
    {
        return $this->belongsTo(TaxRegime::class, 'regime_id');
    }
}
