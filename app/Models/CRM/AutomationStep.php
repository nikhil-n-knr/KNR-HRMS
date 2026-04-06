<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationStep extends Model
{
    protected $table = 'crm_automation_steps';

    protected $fillable = [
        'automation_id',
        'type',
        'action_type',
        'config',
        'step_order',
    ];

    protected $casts = [
        'config' => 'array',
        'step_order' => 'integer',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(MarketingAutomation::class, 'automation_id');
    }
}
