<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRegularization extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'reason',
        'regularized_in_time',
        'regularized_out_time',
        'status',
        'approver_id',
        'approver_remarks'
    ];

    protected $casts = [
        'date' => 'date',
        // Note: regularized_in_time is 'H:i:s', not full timestamp, so no datetime cast unless we parse manually
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
