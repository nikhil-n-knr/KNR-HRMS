<?php

namespace App\Services;

use App\Models\Module;
use App\Models\OrganizationModule;
use App\Models\UserModuleAccess;
use App\Models\ModuleActivationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ModuleAccessService
{
    /**
     * Request module activation (send OTP or use dev key)
     */
    public function requestActivation($tenantId, $moduleId, $userId, $method = 'otp')
    {
        $module = Module::findOrFail($moduleId);
        
        if ($method === 'dev' || $method === 'dev_mode') {
            // Dev mode - no OTP needed, just return request
            return ModuleActivationRequest::create([
                'tenant_id' => $tenantId,
                'module_id' => $moduleId,
                'requested_by' => $userId,
                'otp_code' => '0000',
                'status' => 'pending',
                'expires_at' => now()->addMinutes(15),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
        
        if ($method === 'otp') {
            // Generate OTP
            $otp = $this->generateOTP();
            
            // Create activation request
            $request = ModuleActivationRequest::create([
                'tenant_id' => $tenantId,
                'module_id' => $moduleId,
                'requested_by' => $userId,
                'otp_code' => $otp,
                'expires_at' => now()->addMinutes(15),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Send OTP email
            $user = User::find($userId);
            // TODO: Create ModuleActivationOTP mail class
            // Mail::to($user->email)->send(new ModuleActivationOTP($otp, $module->display_name));
            
            return $request;
        }
        
        return null;
    }
    
    /**
     * Verify OTP and activate module
     */
    public function verifyAndActivate($requestId, $otpCode)
    {
        $request = ModuleActivationRequest::findOrFail($requestId);
        
        // Check if request is still valid
        if (!$request->isValid()) {
            if ($request->expires_at && $request->expires_at->isPast()) {
                $request->update(['status' => 'expired']);
                throw new \Exception('OTP has expired. Please request a new one.');
            }
            
            if ($request->attempts >= 3) {
                throw new \Exception('Too many failed attempts. Please request a new OTP.');
            }
            
            throw new \Exception('This activation request is no longer valid.');
        }
        
        // Verify OTP
        if ($request->otp_code !== $otpCode) {
            $request->increment('attempts');
            throw new \Exception('Invalid OTP code. Please try again.');
        }
        
        // Activate module
        $orgModule = OrganizationModule::updateOrCreate(
            [
                'tenant_id' => $request->tenant_id,
                'module_id' => $request->module_id,
            ],
            [
                'is_activated' => true,
                'activated_at' => now(),
                'activated_by' => $request->requested_by,
                'subscription_status' => 'active',
            ]
        );
        
        $request->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);
        
        return $orgModule;
    }
    
    /**
     * Dev mode activation (key: 0000)
     */
    public function devActivate($tenantId, $moduleId, $userId, $key)
    {
        if ($key !== '0000') {
            throw new \Exception('Invalid dev activation key');
        }
        
        return OrganizationModule::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'module_id' => $moduleId,
            ],
            [
                'is_activated' => true,
                'activated_at' => now(),
                'activated_by' => $userId,
                'subscription_status' => 'active',
                'activation_key' => 'DEV_MODE_0000',
            ]
        );
    }
    
    /**
     * Grant user access to module
     */
    public function grantUserAccess($userId, $moduleId, $role = null, $grantedBy = null)
    {
        $user = User::findOrFail($userId);
        
        // Check if tenant has module activated
        $orgModule = OrganizationModule::where('tenant_id', $user->tenant_id)
            ->where('module_id', $moduleId)
            ->where('is_activated', true)
            ->firstOrFail();
        
        return UserModuleAccess::updateOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $moduleId,
            ],
            [
                'tenant_id' => $user->tenant_id,
                'is_enabled' => true,
                'module_role' => $role,
                'granted_by' => $grantedBy ?? auth()->id(),
                'granted_at' => now(),
            ]
        );
    }
    
    /**
     * Revoke user access to module
     */
    public function revokeUserAccess($userId, $moduleId)
    {
        return UserModuleAccess::where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->update(['is_enabled' => false]);
    }
    
    /**
     * Check if user has module access
     */
    public function userHasAccess($userId, $moduleName)
    {
        $module = Module::where('name', $moduleName)->first();
        
        if (!$module) {
            return false;
        }
        
        // Check if module requires activation
        if (!$module->requires_activation) {
            return true;
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Check tenant has module activated
        $tenantHasModule = OrganizationModule::where('tenant_id', $user->tenant_id)
            ->where('module_id', $module->id)
            ->where('is_activated', true)
            ->where('subscription_status', 'active')
            ->exists();
        
        if (!$tenantHasModule) {
            return false;
        }
        
        // Check user has access
        return UserModuleAccess::where('user_id', $userId)
            ->where('module_id', $module->id)
            ->where('is_enabled', true)
            ->exists();
    }
    
    /**
     * Get all modules for a tenant with activation status
     */
    public function getTenantModules($tenantId)
    {
        return Module::with(['tenants' => function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }])
        ->where('is_active', true)
        ->get()
        ->map(function ($module) use ($tenantId) {
            $tenantModule = $module->tenants->first();
            
            return [
                'id' => $module->id,
                'name' => $module->name,
                'display_name' => $module->display_name,
                'description' => $module->description,
                'icon' => $module->icon,
                'requires_activation' => $module->requires_activation,
                'is_activated' => $tenantModule ? $tenantModule->pivot->is_activated : false,
                'current_users' => $tenantModule ? $tenantModule->pivot->current_users : 0,
                'user_limit' => $tenantModule ? $tenantModule->pivot->user_limit : null,
                'organizations' => $tenantModule ? [[
                    'subscription_status' => $tenantModule->pivot->subscription_status,
                    'is_trial' => $tenantModule->pivot->is_trial,
                    'activated_at' => $tenantModule->pivot->activated_at,
                ]] : [],
            ];
        });
    }
    
    /**
     * Get user's accessible modules
     */
    public function getUserModules($userId)
    {
        return Module::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('is_enabled', true);
        })->get();
    }
    
    /**
     * Generate 6-digit OTP
     */
    private function generateOTP()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
