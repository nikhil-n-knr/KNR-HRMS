<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitPullRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'git_repository_id',
        'external_id',
        'title',
        'state',
        'author_name',
        'reviewers',
        'created_at_provider',
        'merged_at',
        'closed_at'
    ];

    protected $casts = [
        'reviewers' => 'array',
        'created_at_provider' => 'datetime',
        'merged_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function repository()
    {
        return $this->belongsTo(GitRepository::class, 'git_repository_id');
    }
    
    // Links to tasks via pivot too, potentially? For now, we linked commits. 
    // Usually PRs link to tasks too. Migration has task_git_links with git_pull_request_id.
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_git_links', 'git_pull_request_id', 'task_id');
    }
}
