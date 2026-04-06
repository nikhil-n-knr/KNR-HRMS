<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'month',
        'year',
        'batch_name',
        'start_date',
        'end_date',
        'status',
        'total_payout',
        'processed_by',
        'processed_at',
        'workflow_id',
        'current_stage_id',
        'approver_id', // Assigned Approver
        'approved_by', // Actual Action Taker
        'rejection_reason',
        'settings'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_payout' => 'decimal:2',
        'processed_at' => 'datetime',
        'settings' => 'array'
    ];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Workflow Relations
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function currentStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    // The person assigned to approve
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // The person who actually approved
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
