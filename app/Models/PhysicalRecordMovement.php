<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalRecordMovement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'movement_at' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(PhysicalRecord::class, 'record_id');
    }

    public function fromNode()
    {
        return $this->belongsTo(LocationNode::class, 'from_node_id');
    }

    public function toNode()
    {
        return $this->belongsTo(LocationNode::class, 'to_node_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
