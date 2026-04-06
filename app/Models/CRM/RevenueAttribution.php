<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class RevenueAttribution extends Model
{
    protected $table = 'crm_revenue_attribution';

    protected $fillable = [
        'tenant_id',
        'deal_id',
        'campaign_id',
        'channel',
        'touchpoint_type',
        'attributed_amount',
        'interaction_path',
    ];

    protected $casts = [
        'interaction_path' => 'array',
        'attributed_amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }
}
