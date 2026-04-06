<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitUserMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'git_provider_id',
        'git_username',
        'git_email',
        'git_avatar_url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(GitProvider::class, 'git_provider_id');
    }
}
