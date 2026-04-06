<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BugAssignee extends Model
{
    use HasFactory;

    protected $fillable = ['bug_ticket_id', 'assignee_id', 'assignee_type'];

    public function bugTicket()
    {
        return $this->belongsTo(BugTicket::class, 'bug_ticket_id');
    }

    public function assignee()
    {
        return $this->morphTo();
    }
}
