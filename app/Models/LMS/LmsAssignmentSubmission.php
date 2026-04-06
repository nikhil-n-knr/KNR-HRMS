<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsAssignmentSubmission extends Model
{
    protected $table = 'lms_assignment_submissions';

    protected $fillable = [
        'assignment_id', 'activity_id', 'user_id', 'submission_number',
        'status', 'text_content', 'file_paths', 'submission_url',
        'submitted_at', 'score', 'rubric_scores', 'instructor_remarks',
        'plagiarism_flag', 'plagiarism_score', 'graded_by', 'graded_at',
    ];

    protected $casts = [
        'file_paths'      => 'array',
        'rubric_scores'   => 'array',
        'plagiarism_flag' => 'boolean',
        'submitted_at'    => 'datetime',
        'graded_at'       => 'datetime',
    ];

    public function assignment() { return $this->belongsTo(LmsAssignmentV2::class, 'assignment_id'); }
    public function user()       { return $this->belongsTo(\App\Models\User::class); }
    public function gradedBy()   { return $this->belongsTo(\App\Models\User::class, 'graded_by'); }
}
