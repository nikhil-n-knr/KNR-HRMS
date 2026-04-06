<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'answers' => 'array',
        'video_answers' => 'array',
        'reviewers' => 'array',
        'assigned_teams' => 'array',
        'applied_at' => 'datetime',
        'offer_details' => 'array',
        'screening_rating' => 'integer',
        'rejection_notes' => 'string'
    ];

    protected $fillable = [
        'job_posting_id', 'candidate_id', 'resume_path', 'cover_letter', 
        'status', 'score', 'answers', 'video_answers',
        'rejection_reason', 'rejection_notes', 'offer_details',
        'screening_rating', 'screening_feedback', 'screening_recording_url', 'screening_attachment_path',
        'reviewers', 'assigned_teams', 'referrer_id'
    ];

    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function offerLetter()
    {
        return $this->hasOne(OfferLetter::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
