<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'streak_type',
        'current_streak',
        'max_streak',
        'last_incremented_at'
    ];

    protected $casts = [
        'last_incremented_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
