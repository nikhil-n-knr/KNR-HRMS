<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'payslip_number',
        'basic_salary',
        'gross_earnings',
        'gross_deductions',
        'net_pay',
        'payable_days',
        'lop_days',
        'earnings_breakdown',
        'deductions_breakdown',
        'is_held',
        'remarks',
        'status',
        'file_path'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'gross_earnings' => 'decimal:2',
        'gross_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'earnings_breakdown' => 'array',
        'deductions_breakdown' => 'array',
        'is_held' => 'boolean',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
