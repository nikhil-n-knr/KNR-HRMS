<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationExecution extends Model
{
    protected $table = 'crm_automation_executions';

    protected $fillable = [
        'automation_id',
        'contact_id',
        'current_step_id',
        'status',
        'next_execution_at',
    ];

    protected $casts = [
        'next_execution_at' => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(MarketingAutomation::class, 'automation_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(AutomationStep::class, 'current_step_id');
    }
}
