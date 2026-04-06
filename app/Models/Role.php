<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'is_system',
        'version',
        'created_by',
        'dashboard',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withPivot(['data_scope', 'limit_value', 'limit_type'])
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot(['assigned_by', 'valid_from', 'valid_until', 'is_active'])
            ->withTimestamps();
    }
}
