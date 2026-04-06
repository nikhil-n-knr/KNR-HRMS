<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CRMWorkflow extends Model
{
    protected $table = 'crm_workflows';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'entity_type',
        'trigger_event',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(CRMWorkflowStage::class, 'crm_workflow_id')->orderBy('order');
    }
}
