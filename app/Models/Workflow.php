<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'entity_type',
        'trigger_event',
        'is_active',
        'priority',
        'approved_status',
        'rejected_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the stages for this workflow, ordered by stage_order
     */
    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('stage_order');
    }

    /**
     * Get all workflow instances for this workflow
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * Get active workflow for a specific entity type
     */
    public static function getActiveForEntity(string $entityType): ?self
    {
        return static::where('entity_type', $entityType)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();
    }
}
