<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipelineStage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'order',
        'type',
        'win_probability',
        'is_active',
        'is_default',
        'automation_rules',
        'expected_duration_days',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'automation_rules' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
