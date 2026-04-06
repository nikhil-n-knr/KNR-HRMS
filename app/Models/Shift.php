<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'work_days',
        'grace_late_entry',
        'grace_early_exit',
        'break_policy',
        'ip_restrictions',
        'is_default',
        'tenant_id',
        'color',
        'code',
        'location_ids'
    ];

    protected $casts = [
        'work_days' => 'array',
        'break_policy' => 'array',
        'ip_restrictions' => 'array',
        'location_ids' => 'array',
        'is_default' => 'boolean',
    ];
}
