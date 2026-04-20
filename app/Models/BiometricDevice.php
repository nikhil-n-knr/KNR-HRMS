<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
    protected $fillable = [
        'name',
        'serial_number',
        'ip_address',
        'port',
        'username',
        'password',
        'protocol',
        'location_name',
        'description',
        'heartbeat_interval',
        'attendance_zone_id',
        'is_active',
        'is_office_wifi',
        'status',
        'last_sync_at',
        'tenant_id'
    ];

    public function zone()
    {
        return $this->belongsTo(AttendanceZone::class, 'attendance_zone_id');
    }
}
