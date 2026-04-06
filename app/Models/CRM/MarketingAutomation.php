<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingAutomation extends Model
{
    protected $table = 'crm_marketing_automations';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'is_active',
        'trigger_type',
        'trigger_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trigger_config' => 'array',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(AutomationStep::class, 'automation_id')->orderBy('step_order');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(AutomationExecution::class, 'automation_id');
    }
}
