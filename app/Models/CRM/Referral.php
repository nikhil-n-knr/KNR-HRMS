<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class Referral extends Model
{
    use HasFactory;

    protected $table = 'crm_referrals';

    protected $fillable = [
        'tenant_id', 'partner_id', 'referral_link_clicked_at', 
        'ip_address', 'user_agent'
    ];

    protected $casts = [
        'referral_link_clicked_at' => 'datetime'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}
