<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CRMWorkflowStage extends Model
{
    protected $table = 'crm_workflow_stages';

    protected $fillable = [
        'crm_workflow_id',
        'name',
        'order',
        'action_type',
        'config',
    ];

    protected $casts = [
        'order' => 'integer',
        'config' => 'json',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(CRMWorkflow::class, 'crm_workflow_id');
    }
}
