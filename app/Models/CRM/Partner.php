<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class Partner extends Model
{
    use HasFactory;

    protected $table = 'crm_partners';

    protected $fillable = [
        'tenant_id', 'name', 'email', 'referral_code', 
        'website', 'commission_rate', 'status'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'partner_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'partner_id');
    }
}
