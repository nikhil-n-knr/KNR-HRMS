<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbCategory extends Model
{
    use HasFactory;

    protected $table = 'crm_kb_categories';

    protected $fillable = [
        'tenant_id', 'parent_id', 'name', 'slug', 'description'
    ];

    public function parent()
    {
        return $this->belongsTo(KbCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(KbCategory::class, 'parent_id');
    }

    public function articles()
    {
        return $this->hasMany(KbArticle::class, 'category_id');
    }
}
