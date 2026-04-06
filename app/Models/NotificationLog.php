<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'channel',
        'provider',
        'recipient',
        'subject',
        'content',
        'status',
        'retry_count',
        'provider_response',
        'error_message',
        'trace_id',
        'metadata',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'metadata' => 'array',
        'retry_count' => 'integer',
    ];
}
