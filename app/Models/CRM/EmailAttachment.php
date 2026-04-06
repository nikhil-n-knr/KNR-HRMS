<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $table = 'crm_email_attachments';

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function message()
    {
        return $this->belongsTo(EmailMessage::class, 'message_id');
    }
}
