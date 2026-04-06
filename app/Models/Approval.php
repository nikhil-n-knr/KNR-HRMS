<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'approvable_type',
        'approvable_id',
        'requester_id',
        'status',
        'current_stage_order'
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approvable()
    {
        return $this->morphTo();
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class);
    }
}
