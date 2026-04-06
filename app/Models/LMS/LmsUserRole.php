<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsUserRole extends Model
{
    protected $table = 'lms_user_roles';

    protected $fillable = [
        'user_id', 'institution_id', 'program_id', 'role', 'course_access', 'is_active',
    ];

    protected $casts = [
        'course_access' => 'array',
        'is_active'     => 'boolean',
    ];

    public function user()        { return $this->belongsTo(\App\Models\User::class); }
    public function institution() { return $this->belongsTo(LmsInstitution::class); }
}
