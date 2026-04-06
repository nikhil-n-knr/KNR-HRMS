<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appraisal extends Model
{
    protected $fillable = [
        'employee_id', 'appraisal_cycle_id',
        'stage', 
        'self_rating', 'manager_rating', 'final_rating',
        'self_comments', 'manager_comments', 'hr_comments',
        'submitted_at', 'reviewed_at'
    ];

    protected $casts = [
        'submitted_at' => 'date',
        'reviewed_at' => 'date',
        'self_rating' => 'decimal:2',
        'manager_rating' => 'decimal:2',
        'final_rating' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }

    public function cycle()
    {
        return $this->belongsTo(AppraisalCycle::class, 'appraisal_cycle_id');
    }

    public function goalRatings()
    {
        return $this->hasMany(GoalRating::class);
    }
}
