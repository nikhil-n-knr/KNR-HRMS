<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'module_id',
        'is_activated',
        'activated_at',
        'activated_by',
        'activation_key',
        'is_trial',
        'trial_ends_at',
        'subscription_status',
        'subscription_starts_at',
        'subscription_ends_at',
        'user_limit',
        'current_users',
        'settings',
    ];

    protected $casts = [
        'is_activated' => 'boolean',
        'is_trial' => 'boolean',
        'activated_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'settings' => 'array',
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
     * User who activated this module
     */
    public function activatedBy()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    /**
     * Check if module is currently active (not expired)
     */
    public function isCurrentlyActive()
    {
        if (!$this->is_activated) {
            return false;
        }

        if ($this->subscription_status !== 'active') {
            return false;
        }

        if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) {
            return false;
        }

        return true;
    }
}
