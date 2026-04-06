<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsForumPost extends Model
{
    use SoftDeletes;

    protected $table = 'lms_forum_posts';

    protected $fillable = [
        'forum_id', 'user_id', 'title', 'content', 'type',
        'upvotes', 'is_answered', 'accepted_answer_id',
        'is_pinned', 'is_flagged', 'is_visible',
    ];

    protected $casts = [
        'is_answered' => 'boolean',
        'is_pinned'   => 'boolean',
        'is_flagged'  => 'boolean',
        'is_visible'  => 'boolean',
    ];

    public function user()    { return $this->belongsTo(\App\Models\User::class); }
    public function replies() { return $this->hasMany(LmsForumReply::class, 'post_id'); }
}
