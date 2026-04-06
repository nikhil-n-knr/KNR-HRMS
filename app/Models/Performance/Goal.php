<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'employee_id', 'appraisal_cycle_id',
        'title', 'description', 'weightage', 
        'status', 'manager_remarks',
        'progress', 'progress_status'
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }

    public function cycle()
    {
        return $this->belongsTo(AppraisalCycle::class, 'appraisal_cycle_id');
    }
}
