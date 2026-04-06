<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollHold extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'employee_id',
        'reason',
        'type',
        'amount',
        'hold_until',
        'status',
        'released_in_payroll_id',
        'created_by'
    ];
    
    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
