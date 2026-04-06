<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class ChannelStat extends Model
{
    protected $table = 'crm_channel_stats';

    protected $fillable = [
        'tenant_id',
        'channel',
        'spend',
        'revenue',
        'leads_generated',
        'roi_percentage',
        'recorded_at',
    ];

    protected $casts = [
        'spend' => 'decimal:2',
        'revenue' => 'decimal:2',
        'roi_percentage' => 'decimal:2',
        'recorded_at' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
