<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'dob',
        'occupation',
        'phone',
        'is_dependent',
        'is_emergency_contact',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_dependent' => 'boolean',
        'is_emergency_contact' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
