<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class KbArticle extends Model
{
    use HasFactory;

    protected $table = 'crm_kb_articles';

    protected $fillable = [
        'tenant_id', 'category_id', 'author_id', 'title', 'slug', 'content', 'status', 'views'
    ];

    public function category()
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
