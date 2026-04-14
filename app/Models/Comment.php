<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'user_type', 'body', 'is_public', 'commentable_id', 'commentable_type', 'attachments'];

    protected $casts = [
        'attachments' => 'array',
        'is_public' => 'boolean',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function author()
    {
        return $this->morphTo('author', 'user_type', 'user_id');
    }
}
