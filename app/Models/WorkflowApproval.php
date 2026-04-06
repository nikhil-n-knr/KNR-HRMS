<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowApproval extends Model
{
    protected $fillable = [
        'workflow_instance_id',
        'stage_id',
        'approver_id',
        'status',
        'comments',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    /**
     * Get the workflow instance
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * Get the stage
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Mark as approved
     */
    public function approve(User $approver, ?string $comments = null): void
    {
        $this->update([
            'status' => 'approved',
            'comments' => $comments,
            'acted_at' => now(),
        ]);
    }

    /**
     * Mark as rejected
     */
    public function reject(User $approver, string $comments): void
    {
        $this->update([
            'status' => 'rejected',
            'comments' => $comments,
            'acted_at' => now(),
        ]);
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
