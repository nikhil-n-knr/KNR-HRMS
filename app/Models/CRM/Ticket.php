<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_tickets';

    protected $fillable = [
        'tenant_id', 'contact_id', 'assigned_to', 'created_by',
        'subject', 'description', 'status', 'priority', 'due_date',
        'sla_policy_id', 'response_due_at', 'resolve_due_at',
        'first_response_at', 'resolved_at', 'sla_status'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'response_due_at' => 'datetime',
        'resolve_due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function slaPolicy()
    {
        return $this->belongsTo(SupportPolicy::class, 'sla_policy_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}
