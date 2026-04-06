<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeHraDeclaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'fiscal_year',
        'rent_monthly',
        'landlord_name',
        'landlord_pan',
        'rented_address',
        'is_metro_city',
        'status',
        'rejection_reason'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
