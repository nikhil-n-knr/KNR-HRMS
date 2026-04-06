<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitCommit extends Model
{
    use HasFactory;

    protected $fillable = [
        'git_repository_id',
        'hash',
        'message',
        'author_name',
        'author_email',
        'committed_at',
        'additions',
        'deletions'
    ];

    protected $casts = [
        'committed_at' => 'datetime',
    ];

    public function repository()
    {
        return $this->belongsTo(GitRepository::class, 'git_repository_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_git_links', 'git_commit_id', 'task_id');
    }
}
