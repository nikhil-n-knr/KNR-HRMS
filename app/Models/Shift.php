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
        'week_off_rules',
        'grace_late_entry',
        'grace_early_exit',
        'post_shift_auto_checkout_cap_minutes',
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
        'week_off_rules' => 'array',
        'break_policy' => 'array',
        'ip_restrictions' => 'array',
        'location_ids' => 'array',
        'is_default' => 'boolean',
        'post_shift_auto_checkout_cap_minutes' => 'integer',
    ];
}
