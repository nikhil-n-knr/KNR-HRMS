<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory;

    protected $table = 'crm_automation_rules';

    protected $fillable = [
        'tenant_id',
        'name',
        'trigger_event',
        'conditions',
        'actions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'json',
        'actions' => 'json',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
