<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'bank_name',
        'branch_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'bic_code',
        'account_type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
