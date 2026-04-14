<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_log_id',
        'in_time',
        'out_time',
        'in_ip',
        'in_lat',
        'in_long',
        'out_ip',
        'out_lat',
        'out_long',
        'session_type',
        'source',
        'is_manual_entry',
    ];

    protected $casts = [
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'is_manual_entry' => 'boolean',
    ];

    public function log()
    {
        return $this->belongsTo(AttendanceLog::class, 'attendance_log_id');
    }
}
