<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 
        'interest_type', 'interest_rate', 'max_amount_limit', 'max_tenure_months', 
        'eligibility_multiplier', 'is_active', 'policy_settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_amount_limit' => 'decimal:2',
        'eligibility_multiplier' => 'decimal:2',
        'policy_settings' => 'array'
    ];

    public function interestRules() {
        return $this->hasMany(LoanInterestRule::class);
    }
}
