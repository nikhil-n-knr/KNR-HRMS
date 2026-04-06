<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeHealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'blood_group',
        'height_cm',
        'weight_kg',
        'allergies',
        'chronic_conditions',
        'last_checkup_date',
        'next_checkup_due',
        'doctor_name',
        'hospital_name',
        'insurance_provider',
        'policy_number',
        'policy_expiry',
        'coverage_details',
        'disability_status',
        'disability_details',
    ];

    protected $casts = [
        'last_checkup_date' => 'date',
        'next_checkup_due' => 'date',
        'policy_expiry' => 'date',
        'disability_status' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
