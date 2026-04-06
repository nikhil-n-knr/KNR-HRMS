<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeExit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'resignation_date',
        'last_working_day_proposed',
        'last_working_day_approved',
        'reason_type',
        'reason_details',
        'status',
        'notice_period_days',
        'shortfall_days',
        'notice_waiver',
        'settlement_date'
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'last_working_day_proposed' => 'date',
        'last_working_day_approved' => 'date',
        'settlement_date' => 'date',
        'notice_waiver' => 'boolean'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function noDues()
    {
        return $this->hasMany(NoDuesApproval::class, 'exit_id');
    }

    public function fnfItems()
    {
        return $this->hasMany(FnFItem::class, 'exit_id');
    }
}
