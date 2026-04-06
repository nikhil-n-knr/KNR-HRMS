<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailStat extends Model
{
    use HasFactory;

    protected $table = 'crm_email_stats';

    protected $fillable = [
        'message_id',
        'event_type',
        'ip_address',
        'user_agent',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(EmailMessage::class, 'message_id');
    }
}
