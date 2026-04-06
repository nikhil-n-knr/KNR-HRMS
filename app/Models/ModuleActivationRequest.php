<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleActivationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'module_id',
        'requested_by',
        'otp_code',
        'license_key',
        'status',
        'verified_at',
        'expires_at',
        'ip_address',
        'user_agent',
        'attempts',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Tenant (Organization)
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Module
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * User who requested activation
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Check if OTP is still valid
     */
    public function isValid()
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->attempts >= 3) {
            return false;
        }

        return true;
    }
}
