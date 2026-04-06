<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BugTicketTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'bug_ticket_id',
        'from_stage_id',
        'to_stage_id',
        'actor_id',
        'actor_type'
    ];

    public function ticket()
    {
        return $this->belongsTo(BugTicket::class, 'bug_ticket_id');
    }

    public function fromStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'to_stage_id');
    }

    public function actor()
    {
        return $this->morphTo();
    }

    // Keep user for backward compatibility if needed, but it points to actor
    public function user()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
