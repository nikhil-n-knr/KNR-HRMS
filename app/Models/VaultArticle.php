<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VaultArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 
        'title', 
        'slug', 
        'excerpt', 
        'content', 
        'order', 
        'is_featured', 
        'is_client_visible'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_client_visible' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title) . '-' . Str::random(5);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(VaultCategory::class, 'category_id');
    }
}
