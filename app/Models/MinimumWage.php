<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinimumWage extends Model
{
    protected $fillable = [
        'state_code',
        'skill_level',
        'industry',
        'zone',
        'basic_wage',
        'vda',
        'effective_from',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'basic_wage' => 'decimal:2',
        'vda' => 'decimal:2',
    ];
}
