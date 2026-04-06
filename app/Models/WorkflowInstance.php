<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends Model
{
    protected $fillable = [
        'workflow_id',
        'entity_type',
        'entity_id',
        'initiator_id',
        'current_stage_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the workflow definition
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the user who initiated this workflow
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * Get the current stage
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    /**
     * Get all approvals for this instance
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class);
    }

    /**
     * Get pending approvals
     */
    public function pendingApprovals(): HasMany
    {
        return $this->approvals()->where('status', 'pending');
    }

    /**
     * Check if workflow is complete
     */
    public function isComplete(): bool
    {
        return in_array($this->status, ['approved', 'rejected', 'cancelled']);
    }

    /**
     * Check if workflow is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
