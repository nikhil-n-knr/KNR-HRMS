<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarSync extends Model
{
    use HasFactory;

    protected $table = 'crm_calendar_syncs';

    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function getAccessTokenAttribute($value)
    {
        try {
            return $value ? decrypt($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = encrypt($value);
    }

    public function getRefreshTokenAttribute($value)
    {
        try {
            return $value ? decrypt($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setRefreshTokenAttribute($value)
    {
        $this->attributes['refresh_token'] = encrypt($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
