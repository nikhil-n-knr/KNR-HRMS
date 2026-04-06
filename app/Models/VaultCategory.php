<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaultCategory extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'icon', 'order'];

    public function articles()
    {
        return $this->hasMany(VaultArticle::class, 'category_id')->orderBy('order');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
