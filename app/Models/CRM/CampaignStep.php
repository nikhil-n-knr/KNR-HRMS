<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignStep extends Model
{
    use HasFactory;

    protected $table = 'crm_campaign_steps';

    protected $fillable = [
        'campaign_journey_id',
        'type', // 'email', 'wait', 'condition', 'tag'
        'configuration',
        'order_index',
    ];

    protected $casts = [
        'configuration' => 'json',
    ];

    public function journey()
    {
        return $this->belongsTo(CampaignJourney::class, 'campaign_journey_id');
    }
}
