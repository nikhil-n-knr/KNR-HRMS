<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewFeedback extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'interview_id', 'interviewer_id', 'rating', 'recommendation', 'result',
        'summary', 'pros', 'cons', 'recording_path', 'attachment_path', 'recording_url'
    ];

    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
