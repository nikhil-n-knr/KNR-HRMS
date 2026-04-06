<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'submodule',
        'action',
        'description',
        'ai_hint',
    ];

    protected $casts = [
        'ai_hint' => 'array',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return str_replace('_', ' ', $this->submodule . ' ' . $this->action);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
