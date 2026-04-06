<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePersonalDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'dob',
        'gender',
        'marital_status',
        'nationality',
        'blood_group',
        'religion',
        'current_address',
        'current_city',
        'current_state',
        'current_zip',
        'current_country',
        'is_permanent_same',
        'permanent_address',
        'permanent_city',
        'permanent_state',
        'permanent_zip',
        'permanent_country',
        'passport_number',
        'passport_expiry',
        'national_id_number',
    ];

    protected $casts = [
        'dob' => 'date',
        'passport_expiry' => 'date',
        'is_permanent_same' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
