<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignJourney extends Model
{
    use HasFactory;

    protected $table = 'crm_campaign_journeys';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'enrollment_criteria',
        'status',
    ];

    protected $casts = [
        'enrollment_criteria' => 'json',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function steps()
    {
        return $this->hasMany(CampaignStep::class, 'campaign_journey_id')->orderBy('order_index');
    }
}
