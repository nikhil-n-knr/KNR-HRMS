<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
    protected $guarded = [];

    public function zone()
    {
        return $this->belongsTo(AttendanceZone::class, 'attendance_zone_id');
    }
}
