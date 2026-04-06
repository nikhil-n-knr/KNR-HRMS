<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprintLog extends Model
{
    protected $fillable = ['sprint_id', 'user_id', 'action', 'details'];

    protected $casts = [
        'details' => 'array',
    ];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
