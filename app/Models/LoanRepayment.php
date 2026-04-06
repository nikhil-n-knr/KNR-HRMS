<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id', 'scheduled_date', 'amount', 'status', 'payroll_id',
        'principal_component', 'interest_component', 'outstanding_balance'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'amount' => 'decimal:2',
        'principal_component' => 'decimal:2',
        'interest_component' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }
}
