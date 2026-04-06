<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BugTicket extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'module_id',
        'task_id',
        'reporter_id',
        'reporter_type',
        'assignee_id',
        'assignee_type',
        'severity',
        'priority',
        'workflow_stage_id',
        'environment_metadata',
        'steps_to_reproduce',
        'resolution_summary',
        'is_client_visible',
        'subject',
        'description',
        'attachments',
        'hours_spent', // Phase 10
        'started_at',
        'resolved_at',
        'rating',
        'rating_feedback',
        'sla_due_at',
        'is_sla_breached',
        'custom_view_tags'
    ];

    protected $casts = [
        'environment_metadata' => 'array',
        'attachments' => 'array',
        'is_client_visible' => 'boolean',
        'is_sla_breached' => 'boolean',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'sla_due_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function module()
    {
        return $this->belongsTo(ProjectModule::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class); // Assuming Task model is App\Models\Task
    }

    public function stage()
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function reporter()
    {
        return $this->morphTo();
    }

    public function assignee()
    {
        return $this->morphTo();
    }

    public function assignees()
    {
        return $this->hasMany(BugAssignee::class, 'bug_ticket_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'desc');
    }

    public function activities()
    {
        return $this->hasMany(BugActivity::class)->orderBy('created_at', 'desc');
    }

    public function forensics()
    {
        return $this->hasOne(ProjectManagement\BugForensics::class);
    }

    public function transitions()
    {
        return $this->hasMany(BugTicketTransition::class)->orderBy('created_at', 'asc');
    }

    public function media()
    {
        return $this->morphMany(BugAttachment::class, 'attachable');
    }

    public function pendingApproval()
    {
        return $this->hasOne(WorkflowApproval::class, 'stage_id', 'workflow_stage_id')
            ->where('status', 'pending')
            ->whereHas('workflowInstance', function($q) {
                $q->where('entity_type', self::class)->whereColumn('entity_id', 'bug_tickets.id');
            });
    }
}
