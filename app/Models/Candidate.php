<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'skills' => 'array',
        'social_links' => 'array',
    ];

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
