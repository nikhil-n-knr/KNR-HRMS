<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TicketMessage extends Model
{
    use HasFactory;

    protected $table = 'crm_ticket_messages';

    protected $fillable = [
        'ticket_id', 'user_id', 'sender_type', 'message', 'is_internal', 'attachments'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
