<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    use HasFactory;

    protected $table = 'crm_email_messages';

    protected $fillable = [
        'thread_id',
        'message_id',
        'from_email',
        'from_name',
        'to_emails',
        'cc_emails',
        'bcc_emails',
        'body_html',
        'body_text',
        'direction',
        'status',
        'is_manual',
        'source',
        'sent_at',
        'resend_id',
    ];

    protected $casts = [
        'to_emails' => 'json',
        'cc_emails' => 'json',
        'bcc_emails' => 'json',
        'sent_at' => 'datetime',
    ];

    public function thread()
    {
        return $this->belongsTo(EmailThread::class);
    }

    public function stats()
    {
        return $this->hasMany(EmailStat::class, 'message_id');
    }

    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class, 'message_id');
    }
}
