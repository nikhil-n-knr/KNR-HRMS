<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsInstitution extends Model
{
    use SoftDeletes;

    protected $table = 'lms_institutions';

    protected $fillable = [
        'parent_id', 'name', 'code', 'type', 'logo_path',
        'primary_color', 'secondary_color', 'website',
        'contact_email', 'contact_phone', 'address',
        'settings', 'is_active', 'depth', 'path',
    ];

    protected $casts = [
        'settings'  => 'array',
        'is_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function programs()
    {
        return $this->hasMany(LmsProgram::class, 'institution_id');
    }

    public function courses()
    {
        return $this->hasMany(\App\Models\LmsCourse::class, 'institution_id');
    }

    public function userRoles()
    {
        return $this->hasMany(LmsUserRole::class, 'institution_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Get all ancestor IDs including self (for permission scoping)
     */
    public function getAncestorIds(): array
    {
        if (empty($this->path)) {
            return [$this->id];
        }
        return array_map('intval', explode('/', $this->path));
    }

    /**
     * Get all descendant institution IDs (used for analytics drill-down)
     */
    public function getDescendantIds(): array
    {
        return self::where('path', 'like', $this->path . '/' . $this->id . '%')
            ->orWhere('path', 'like', '%/' . $this->id . '/%')
            ->pluck('id')
            ->push($this->id)
            ->unique()
            ->toArray();
    }

    /**
     * Boot: auto-compute depth and path on create/update
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $inst) {
            if ($inst->parent_id) {
                $parent = self::find($inst->parent_id);
                $inst->depth = $parent ? $parent->depth + 1 : 0;
                $inst->path  = $parent ? ($parent->path ? $parent->path . '/' . $parent->id : (string)$parent->id) : '';
            }
        });

        static::updating(function (self $inst) {
            if ($inst->isDirty('parent_id')) {
                $parent = $inst->parent_id ? self::find($inst->parent_id) : null;
                $inst->depth = $parent ? $parent->depth + 1 : 0;
                $inst->path  = $parent ? ($parent->path ? $parent->path . '/' . $parent->id : (string)$parent->id) : '';
            }
        });
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
