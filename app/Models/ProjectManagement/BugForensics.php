<?php

namespace App\Models\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BugTicket;

class BugForensics extends Model

{
    use HasFactory;

    protected $table = 'bug_forensics';

    protected $fillable = [
        'bug_ticket_id',
        'session_recording',
        'browser_metadata',
        'console_logs',
        'git_branch',
        'git_latest_commit',
    ];

    protected $casts = [
        'browser_metadata' => 'array',
        'console_logs' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(BugTicket::class, 'bug_ticket_id');
    }
}
