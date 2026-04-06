<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_id',
        'stage_id',
        'approver_id',
        'status',
        'actioned_at',
        'comments'
    ];

    protected $casts = [
        'actioned_at' => 'datetime'
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class);
    }

    public function stage()
    {
        return $this->belongsTo(WorkflowStage::class, 'stage_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
