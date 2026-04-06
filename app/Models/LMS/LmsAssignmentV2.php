<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsAssignmentV2 extends Model
{
    protected $table = 'lms_assignments_v2';

    protected $fillable = [
        'activity_id', 'title', 'description', 'rubric', 'max_score', 'pass_mark',
        'due_date', 'max_file_size_mb', 'allowed_file_types',
        'allow_text_submission', 'allow_file_upload', 'allow_url_submission',
        'allow_resubmission', 'max_resubmissions',
        'enable_plagiarism_check', 'peer_review_enabled',
    ];

    protected $casts = [
        'rubric'                => 'array',
        'allowed_file_types'    => 'array',
        'allow_text_submission' => 'boolean',
        'allow_file_upload'     => 'boolean',
        'allow_url_submission'  => 'boolean',
        'allow_resubmission'    => 'boolean',
        'enable_plagiarism_check' => 'boolean',
        'peer_review_enabled'   => 'boolean',
        'due_date'              => 'datetime',
    ];

    public function activity()    { return $this->belongsTo(LmsActivity::class); }
    public function submissions() { return $this->hasMany(LmsAssignmentSubmission::class, 'assignment_id'); }
}
