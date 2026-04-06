<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitPrReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'git_pull_request_id',
        'external_id',
        'reviewer_name',
        'reviewer_username',
        'employee_id',
        'state',
        'submitted_at',
        'comments_count'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function pullRequest()
    {
        return $this->belongsTo(GitPullRequest::class, 'git_pull_request_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
