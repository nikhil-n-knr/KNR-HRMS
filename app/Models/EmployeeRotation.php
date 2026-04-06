<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_rotation_id',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rotation()
    {
        // Assuming the logic table is shift_rotations
        return $this->belongsTo(ShiftRotation::class, 'shift_rotation_id');
    }
}
