<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'employee_id',
        'incurred_date',
        'expense_category_id',
        'project_id',
        'client_id',
        'amount',
        'description',
        'receipt_path',
        'status',
        'current_stage_id',
        'rejection_reason',
        'is_billable',
        'approved_by',
        'reimbursed_on',
        'reimbursed_on',
        'payout_method',
        'payroll_id',
        'category',
        'currency',
        'exchange_rate',
        'gst_number',
        'approved_amount',
        'is_duplicate_flag'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'incurred_date' => 'date',
        'is_billable' => 'boolean'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function currentStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
