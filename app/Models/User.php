<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'client_id',
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'employee_id',
        'employee_type',
        'department_id',
        'location_id',
        'team_id',
        'manager_id',
        'mfa_enabled',
        'mfa_secret',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
        'resource_locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'mfa_enabled' => 'boolean',
        'resource_locked_until' => 'datetime',
        'preferences' => 'array',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot(['assigned_by', 'valid_from', 'valid_until', 'is_active'])
            ->withTimestamps();
    }

    public function scopes()
    {
        return $this->hasMany(UserScope::class);
    }
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function client() {
        return $this->belongsTo(Client::class);
    }
    
    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function location() {
        return $this->belongsTo(Location::class);
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }

    public function managedTeams() {
        return $this->hasMany(Team::class, 'manager_id');
    }

    public function employee() {
        return $this->hasOne(Employee::class);
    }
    
    /**
     * Modules accessible to this user
     */
    public function modules() {
        return $this->belongsToMany(Module::class, 'user_module_access')
            ->withPivot(['is_enabled', 'module_role', 'granted_by', 'granted_at'])
            ->withTimestamps();
    }
    
    /**
     * Get the data scope for a specific permission key.
     * Returns: 'global', 'tenant', 'department', 'team', 'self' or null if not permitted.
     */
    public function getPermissionScope(string $permissionName): ?string
    {
        // 1. Super Admin Check
        if ($this->is_super_admin || ($this->roles->contains('name', 'Super Admin'))) {
            return 'global';
        }

        // 2. Resolve Permission ID
        // optimized: Cache this eventually
        $permission = \Illuminate\Support\Facades\DB::table('permissions')
            ->where('module', explode('.', $permissionName)[0] ?? '')
            ->where('submodule', explode('.', $permissionName)[1] ?? '')
            ->where('action', explode('.', $permissionName)[2] ?? '') // approximate matching logic
            ->first();
            
        // Better: just match the constructed name if stored, but DB structure is split.
        // Or query logic in AuthServiceProvider used split. 
        // Let's assume we can query by parts.
        $parts = explode('.', $permissionName);
        if (count($parts) !== 3) return null; // Invalid key format
        
        // We need to query the pivot table via Roles
        // We want the "widest" scope if user has multiple roles with same permission.
        // Scope Hierarchy: global > tenant > department > team > self
        
        $roleIds = $this->roles->pluck('id');
        
        $scopes = \Illuminate\Support\Facades\DB::table('role_permission')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->whereIn('role_permission.role_id', $roleIds)
            ->where('permissions.module', $parts[0])
            ->where('permissions.submodule', $parts[1])
            ->where('permissions.action', $parts[2])
            ->pluck('role_permission.data_scope')
            ->toArray();
            
        if (empty($scopes)) return null; // Permission not assigned
        
        // Resolve best scope
        if (in_array('global', $scopes)) return 'global';
        if (in_array('tenant', $scopes)) return 'tenant';
        if (in_array('department', $scopes)) return 'department';
        if (in_array('team', $scopes)) return 'team';
        
        return 'self';
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // 1. Super Admin Bypass
        if ($this->roles->contains('name', 'Super Admin')) {
            return true;
        }

        // 2. Check Roles
        foreach ($this->roles as $role) {
            // Optimization: Load permissions with roles in auth middleware to avoid N+1
            if ($role->permissions->contains(function ($p) use ($permission) {
                return "{$p->module}.{$p->submodule}.{$p->action}" === $permission;
            })) {
                return true;
            }
        }

        return false;
    }
    /**
     * Check if user has a specific role or any of an array of roles.
     * 
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_array($roles)) {
            return $this->roles->whereIn('name', $roles)->isNotEmpty();
        }

        return $this->roles->contains('name', $roles);
    }
    public function identityCard()
    {
        return $this->hasOne(IdentityCard::class);
    }

    /**
     * Assign a role to the user.
     * @param string $roleName
     */
    public function assignRole($roleName)
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->syncWithoutDetaching([
                $role->id => [
                    'assigned_by' => 1, // System
                    'valid_from' => now(),
                    'is_active' => true
                ]
            ]);
        }
    }

    public function assignedDeals()
    {
        return $this->hasMany(\App\Models\CRM\Deal::class, 'assigned_to');
    }

    public function assignedTickets()
    {
        return $this->hasMany(\App\Models\CRM\Ticket::class, 'assigned_to');
    }

    /**
     * Visitors hosted by this user
     */
    public function hostedVisitors()
    {
        return $this->hasMany(Visitor::class, 'host_id');
    }

    /**
     * All visitor passes associated with visitors hosted by this user
     */
    public function visitorPasses()
    {
        return $this->hasManyThrough(
            VisitorPass::class,
            Visitor::class,
            'host_id',       // Foreign key on visitors table...
            'visitor_id',    // Foreign key on visitor_passes table...
            'id',            // Local key on users table...
            'id'             // Local key on visitors table...
        );
    }
    public function crmEmailAccounts()
    {
        return $this->belongsToMany(\App\Models\CRM\EmailAccount::class, 'crm_email_account_user')->withTimestamps();
    }

    public function crmEmails()
    {
        return $this->hasManyThrough(
            \App\Models\CRM\EmailMessage::class,
            \App\Models\CRM\EmailThread::class,
            'email_account_id', // Found this logic after further checking EmailThread -> EmailAccount
            'thread_id',
            'id', // Local key on users... wait, User has many EmailAccounts.
            'id'
        );
    }

    /**
     * LMS Relationships
     */
    public function lmsRoles()       { return $this->hasMany(\App\Models\LMS\LmsUserRole::class); }
    public function lmsEnrollments() { return $this->hasMany(\App\Models\LMS\LmsEnrollment::class); }
    public function lmsProgress()    { return $this->hasMany(\App\Models\LMS\LmsCourseProgress::class); }
    public function lmsCertificates(){ return $this->hasMany(\App\Models\LMS\LmsCertificateV2::class); }
    public function lmsPoints()      { return $this->hasMany(\App\Models\LMS\LmsUserPoints::class); }
    public function lmsSubscriptions(){ return $this->hasMany(\App\Models\LMS\LmsSubscription::class); }
    public function lmsTransactions() { return $this->hasMany(\App\Models\LMS\LmsTransaction::class); }
}
