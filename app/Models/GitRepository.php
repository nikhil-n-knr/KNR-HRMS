<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitRepository extends Model
{
    use HasFactory;

    protected $fillable = [
        'git_provider_id',
        'project_id',
        'external_id',
        'name',
        'url',
        'clone_url',
        'branch_filter',
        'path_rules',
        'webhook_secret',
        'is_active',
        'last_synced_at'
    ];

    protected $casts = [
        'branch_filter' => 'array',
        'path_rules' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(GitProvider::class, 'git_provider_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function commits()
    {
        return $this->hasMany(GitCommit::class);
    }

    public function pullRequests()
    {
        return $this->hasMany(GitPullRequest::class);
    }
}
