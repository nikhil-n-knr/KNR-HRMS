<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SLATarget extends Model
{
    protected $table = 'crm_sla_targets';

    protected $fillable = [
        'policy_id',
        'priority',
        'response_time_minutes',
        'resolve_time_minutes',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SupportPolicy::class, 'policy_id');
    }
}
