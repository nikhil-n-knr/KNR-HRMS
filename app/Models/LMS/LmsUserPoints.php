<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsUserPoints extends Model
{
    protected $table = 'lms_user_points';
    protected $fillable = [
        'user_id', 'course_id', 'action', 'points', 'description', 'metadata',
    ];
    protected $casts = [
        'metadata'   => 'array',
    ];
    public function user() { return $this->belongsTo(\App\Models\User::class); }
}
