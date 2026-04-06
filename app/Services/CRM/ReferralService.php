<?php

namespace App\Services\CRM;

use App\Models\CRM\Partner;
use App\Models\CRM\Referral;
use App\Models\CRM\Lead;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReferralService
{
    /**
     * Generate a unique referral code for a partner.
     */
    public function generateCode($name)
    {
        $code = Str::slug($name) . '-' . Str::random(5);
        
        while (Partner::where('referral_code', $code)->exists()) {
            $code = Str::slug($name) . '-' . Str::random(5);
        }
        
        return strtoupper($code);
    }

    /**
     * Record a referral link click.
     */
    public function recordClick($referralCode, $ip, $userAgent)
    {
        $partner = Partner::where('referral_code', $referralCode)->first();
        
        if ($partner) {
            Referral::create([
                'tenant_id' => $partner->tenant_id,
                'partner_id' => $partner->id,
                'referral_link_clicked_at' => Carbon::now(),
                'ip_address' => $ip,
                'user_agent' => $userAgent
            ]);
            
            return $partner;
        }
        
        return null;
    }

    /**
     * Attribute a lead to a partner based on a referral code.
     */
    public function attributeLead(Lead $lead, $referralCode)
    {
        $partner = Partner::where('referral_code', $referralCode)->first();
        
        if ($partner) {
            $lead->update(['partner_id' => $partner->id]);
            return true;
        }
        
        return false;
    }
}
