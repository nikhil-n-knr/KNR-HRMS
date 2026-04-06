<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class MarketingFlow extends Model
{
    protected $table = 'crm_marketing_flows';

    protected $fillable = [
        'tenant_id',
        'name',
        'nodes',
        'edges',
        'total_executions',
        'conversions',
        'revenue_impact',
        'is_active',
    ];

    protected $casts = [
        'nodes' => 'array',
        'edges' => 'array',
        'revenue_impact' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
