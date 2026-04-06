<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'contact_person',
        'email',
        'portal_access',
        'contract_start',
        'contract_end',
    ];

    protected $casts = [
        'portal_access' => 'boolean',
        'contract_start' => 'date',
        'contract_end' => 'date',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function clientUsers()
    {
        return $this->hasMany(\App\Models\ClientUser::class);
    }
}
