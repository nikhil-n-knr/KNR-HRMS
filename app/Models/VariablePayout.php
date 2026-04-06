<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariablePayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'type',
        'remarks',
        'pay_month',
        'pay_year',
        'status',
        'payroll_id',
        'created_by',
        'approved_by'
    ];
    
    public function employee() {
        return $this->belongsTo(Employee::class);
    }
    
    public function creator() {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
