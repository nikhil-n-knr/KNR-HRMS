<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalCycle extends Model
{
    protected $fillable = [
        'name', 'start_date', 'end_date', 'self_review_deadline', 
        'status', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'self_review_deadline' => 'date',
        'is_active' => 'boolean'
    ];

    public function appraisals()
    {
        return $this->hasMany(Appraisal::class);
    }
}
