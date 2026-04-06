<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_name',
        'api_token',
        'allowed_ip',
        'last_active_at',
        'tenant_id'
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    protected $hidden = [
        'api_token',
    ];
}
