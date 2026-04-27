<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalRecord extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'received_at' => 'datetime'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function location() { return $this->belongsTo(PhysicalDocumentLocation::class); }
    public function currentLocationNode() { return $this->belongsTo(LocationNode::class, 'current_location_node_id'); }
    public function locationAssignments() { return $this->morphMany(LocationAssignment::class, 'entity'); }
    public function movements() { return $this->hasMany(PhysicalRecordMovement::class, 'record_id')->orderByDesc('movement_at'); }
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by'); }
}
