<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsForumReply extends Model
{
    use SoftDeletes;

    protected $table = 'lms_forum_replies';

    protected $fillable = [
        'post_id', 'parent_id', 'user_id', 'content',
        'upvotes', 'is_accepted_answer', 'is_flagged', 'is_visible',
    ];

    protected $casts = [
        'is_accepted_answer' => 'boolean',
        'is_flagged'         => 'boolean',
        'is_visible'         => 'boolean',
    ];

    public function post()     { return $this->belongsTo(LmsForumPost::class); }
    public function parent()   { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function user()     { return $this->belongsTo(\App\Models\User::class); }
}
