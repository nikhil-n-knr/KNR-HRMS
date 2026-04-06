<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rules',
        'late_mark_threshold',
        'deduction_rule',
        'overtime_policy',
        'wfh_policy',
        'timesheet_policy',
        'sandwich_rule_enabled',
        'priority',
        'tenant_id'
    ];

    protected $casts = [
        'rules' => 'array',
        'deduction_rule' => 'array',
        'overtime_policy' => 'array',
        'wfh_policy' => 'array',
        'timesheet_policy' => 'array',
        'sandwich_rule_enabled' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
