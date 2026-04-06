<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAnalysisLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'analyzable_type', 'analyzable_id',
        'type', 'severity', 'confidence_score',
        'analysis_data', 'summary', 'is_reviewed'
    ];

    protected $casts = [
        'analysis_data' => 'array',
        'is_reviewed' => 'boolean',
        'confidence_score' => 'float'
    ];

    public function analyzable()
    {
        return $this->morphTo();
    }
}
