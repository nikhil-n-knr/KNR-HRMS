<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'loan_product_id', 'loan_type', 
        'principal_amount', 'tenure_months', 'reason', 
        'status', 'monthly_installment', 'interest_rate',
        'interest_type_applied', 'interest_rate_applied',
        'approved_by', 'approved_at', 'disbursement_date',
        'signed_at', 'signer_ip', 'signature_hash',
        'risk_score', 'risk_analysis',
        'is_paused', 'paused_until', 'foreclosure_date', 'foreclosure_amount',
        'is_resignation_recovery'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'disbursement_date' => 'date',
        'signed_at' => 'datetime',
        'interest_rate_applied' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'risk_analysis' => 'array',
        'foreclosure_amount' => 'decimal:2',
        'foreclosure_date' => 'date',
        'paused_until' => 'date',
        'is_paused' => 'boolean',
        'is_resignation_recovery' => 'boolean'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function product()
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
