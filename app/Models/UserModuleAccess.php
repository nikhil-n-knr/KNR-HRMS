<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModuleAccess extends Model
{
    use HasFactory;

    protected $table = 'user_module_access';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'module_id',
        'is_enabled',
        'granted_by',
        'granted_at',
        'module_role',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'granted_at' => 'datetime',
    ];

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
     * User who granted access
     */
    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
