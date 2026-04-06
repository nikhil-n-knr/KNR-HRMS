<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'region',
        'address',
        'city',
        'state',
        'country',
        'pt_enabled',
        'lwf_enabled',
        'is_hq',
        'state_code',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(JobPosting::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
