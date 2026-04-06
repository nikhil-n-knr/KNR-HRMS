<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationInteraction extends Model
{
    use HasFactory;

    public $timestamps = false; // We only have created_at

    protected $fillable = [
        'notification_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'details',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'details' => 'array'
    ];
}
