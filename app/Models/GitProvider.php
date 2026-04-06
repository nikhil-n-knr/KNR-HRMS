<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class GitProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'access_token',
        'refresh_token',
        'client_id',
        'client_secret',
        'base_url',
        'is_active',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'client_secret',
    ];

    // Encryption Mutators/Accessors
    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    public function getAccessTokenAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null; 
        }
    }

    public function setRefreshTokenAttribute($value)
    {
        if ($value) $this->attributes['refresh_token'] = Crypt::encryptString($value);
    }

    public function getRefreshTokenAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function repositories()
    {
        return $this->hasMany(GitRepository::class);
    }
}
