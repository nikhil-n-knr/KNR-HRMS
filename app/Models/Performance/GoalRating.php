<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalRating extends Model
{
    protected $fillable = [
        'appraisal_id', 'goal_id',
        'self_rating', 'self_remarks',
        'manager_rating', 'manager_remarks'
    ];

    protected $casts = [
        'self_rating' => 'decimal:2',
        'manager_rating' => 'decimal:2',
    ];

    public function appraisal()
    {
        return $this->belongsTo(Appraisal::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }
}
