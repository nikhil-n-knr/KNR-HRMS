<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'department_id',
        'type', // Department Name or Asset Type
        'module', // 'it', 'finance'
        'status', // Pending, Cleared, Rejected
        'due_amount',
        'remarks',
        'cleared_by',
        'cleared_at'
    ];

    protected $casts = [
        'cleared_at' => 'datetime',
        'due_amount' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }
}
