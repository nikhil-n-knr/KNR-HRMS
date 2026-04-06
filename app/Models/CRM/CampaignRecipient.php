<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $table = 'crm_campaign_recipients';

    protected $fillable = [
        'campaign_id', 'contact_id', 'recipient_email', 'recipient_name',
        'status', 'sent_at', 'opened_at', 'clicked_at', 'error_message'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
