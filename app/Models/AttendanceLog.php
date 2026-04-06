<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'status',
        'is_late',
        'is_half_day',
        'is_regularized',
        'total_work_minutes',
        'total_break_minutes',
        'overtime_minutes',
        'late_minutes',
        'early_leaving_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_late' => 'boolean',
        'is_half_day' => 'boolean',
        'is_regularized' => 'boolean',
    ];

    public function sessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
