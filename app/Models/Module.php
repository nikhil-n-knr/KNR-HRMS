<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'is_premium',
        'is_active',
        'base_price',
        'requires_activation',
        'activation_type',
        'settings',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'requires_activation' => 'boolean',
        'base_price' => 'decimal:2',
        'settings' => 'array',
    ];

    /**
     * Tenants that have this module
     */
    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'organization_modules')
            ->withPivot([
                'is_activated', 'activated_at', 'activated_by',
                'subscription_status', 'user_limit', 'current_users',
                'is_trial', 'trial_ends_at', 'activation_key',
                'subscription_starts_at', 'subscription_ends_at', 'settings'
            ])
            ->withTimestamps();
    }

    /**
     * Users that have access to this module
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_module_access')
            ->withPivot(['is_enabled', 'module_role', 'granted_at'])
            ->withTimestamps();
    }

    /**
     * Activation requests for this module
     */
    public function activationRequests()
    {
        return $this->hasMany(ModuleActivationRequest::class);
    }
}
