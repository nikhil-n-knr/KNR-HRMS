<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'actor_id',
        'old_values',
        'new_values',
        'remarks'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
