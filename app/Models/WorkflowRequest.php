<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requestable_type', 'requestable_id', 
        'workflow_id', 'current_stage_id', 
        'status', 'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function requestable()
    {
        return $this->morphTo();
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function currentStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    public function logs()
    {
        return $this->hasMany(WorkflowLog::class);
    }
}
