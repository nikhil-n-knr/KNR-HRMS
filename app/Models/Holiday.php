<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'type',
        'applies_to_locations',
        'is_recurring',
        'tenant_id'
    ];

    protected $casts = [
        'date' => 'date',
        'applies_to_locations' => 'array',
        'is_recurring' => 'boolean',
    ];
}
