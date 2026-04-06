<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignEvent extends Model
{
    use HasFactory;
    
    protected $table = 'crm_campaign_events';

    protected $fillable = [
        'campaign_id', 'contact_id', 'event_type', 'ip_address', 'user_agent', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    protected static function booted()
    {
        static::created(function ($event) {
            $campaign = $event->campaign;
            $stats = $campaign->stats ?? [
                'total' => 0,
                'sent' => 0,
                'opened' => 0,
                'clicked' => 0,
                'bounced' => 0
            ];

            if ($event->event_type === 'open') {
                $stats['opened']++;
            } elseif ($event->event_type === 'click') {
                $stats['clicked']++;
            } elseif ($event->event_type === 'bounce') {
                $stats['bounced']++;
            }

            $campaign->update(['stats' => $stats]);
        });
    }
}
