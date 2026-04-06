<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientEnvironmentPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'client_type',
        'device_name',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function client()
    {
        return $this->morphTo();
    }
}
