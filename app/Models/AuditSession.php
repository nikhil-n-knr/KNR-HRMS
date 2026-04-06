<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditSession extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'stats' => 'array',
        'completed_at' => 'datetime'
    ];

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(AuditItem::class);
    }
}
