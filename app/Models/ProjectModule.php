<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'description',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Parent Module
     */
    public function parent()
    {
        return $this->belongsTo(ProjectModule::class, 'parent_id');
    }

    /**
     * Sub-Modules (Children)
     */
    public function children()
    {
        return $this->hasMany(ProjectModule::class, 'parent_id');
    }

    /**
     * Infinitely Deep Sub-Modules
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'module_id');
    }

    public function bugs()
    {
        return $this->hasMany(BugTicket::class, 'module_id');
    }
}
