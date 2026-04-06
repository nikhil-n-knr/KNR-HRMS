<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailThread extends Model
{
    use HasFactory;

    protected $table = 'crm_email_threads';

    protected $fillable = [
        'tenant_id',
        'email_account_id', // Added this line
        'subject',
        'trackable_type',
        'trackable_id',
        'external_thread_id',
        'mapping_status'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account() // Added this method
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function trackable()
    {
        return $this->morphTo();
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'thread_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(EmailMessage::class, 'thread_id')->latest();
    }
}
