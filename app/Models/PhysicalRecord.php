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
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by'); }
}
