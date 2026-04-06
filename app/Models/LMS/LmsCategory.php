<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LmsCategory extends Model
{
    use SoftDeletes;

    protected $table = 'lms_categories';

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description',
        'icon', 'color', 'thumbnail_path',
        'depth', 'path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'depth'      => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
            if ($cat->parent_id) {
                $parent = self::find($cat->parent_id);
                $cat->depth = $parent ? $parent->depth + 1 : 0;
                $cat->path  = $parent ? ($parent->path ? $parent->path . '/' . $parent->id : (string)$parent->id) : '';
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function courses()
    {
        return $this->hasMany(\App\Models\LmsCourse::class, 'category_id');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all category IDs in this subtree (for filtering courses)
     */
    public function getSubtreeIds(): array
    {
        $ids = [$this->id];
        $this->loadMissing('allChildren');
        foreach ($this->allChildren as $child) {
            $ids = array_merge($ids, $child->getSubtreeIds());
        }
        return $ids;
    }
}
