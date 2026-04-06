<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsCourseContent extends Model
{
    protected $table = 'lms_course_content';
    
    protected $fillable = [
        'course_id', 'order', 'type', 'title', 'description',
        'file_path', 'content', 'min_time_seconds', 'is_mandatory'
    ];
    
    protected $casts = [
        'is_mandatory' => 'boolean',
    ];
    
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }
    
    public function questions()
    {
        return $this->hasMany(LmsQuestion::class, 'content_id');
    }
}
