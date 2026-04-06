<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceStateRule extends Model
{
    protected $fillable = [
        'state_code',
        'component',
        'rules',
        'is_active',
    ];

    protected $casts = [
        'rules' => 'array',
        'is_active' => 'boolean',
    ];
}
