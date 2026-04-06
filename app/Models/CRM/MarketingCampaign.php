<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CRM\ContactSegment;
use App\Models\CRM\MarketingTemplate;
use App\Models\CRM\CampaignRecipient;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $table = 'crm_marketing_campaigns';

    protected $fillable = [
        'tenant_id', 'name', 'subject', 'status', 'scheduled_at', 'sent_at',
        'template_id', 'content', 'segment_id', 'type', 'stats', 
        'predicted_success_score', 'ai_optimization_tips', 'created_by'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'stats' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template()
    {
        return $this->belongsTo(MarketingTemplate::class);
    }

    public function segment()
    {
        return $this->belongsTo(ContactSegment::class);
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }
}
