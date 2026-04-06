<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'salary_structure_id',
        'annual_ctc',
        'effective_date',
        'breakdown',
        'is_active',
        'remarks'
    ];

    protected $casts = [
        'annual_ctc' => 'decimal:2',
        'effective_date' => 'date',
        'breakdown' => 'array',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function structure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }
}
