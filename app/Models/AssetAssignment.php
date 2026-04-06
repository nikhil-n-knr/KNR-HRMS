<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
        'quantity' => 'integer'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function asset() { return $this->belongsTo(Asset::class); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by'); }
}
