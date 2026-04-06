<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoDuesApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'exit_id',
        'department', 
        'approver_id',
        'status',
        'recovery_amount',
        'remarks'
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    
    public function exitRecord()
    {
        return $this->belongsTo(EmployeeExit::class, 'exit_id');
    }
}
