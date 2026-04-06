<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'questions' => 'array',
    ];

    public function jobs()
    {
        return $this->hasMany(JobPosting::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
