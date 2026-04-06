<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStage extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public function assignees()
    {
        return $this->belongsToMany(\App\Models\Employee::class, 'project_stage_assignees', 'project_stage_id', 'employee_id')
                    ->withPivot('notify_on_entry')
                    ->withTimestamps();
    }

    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'type',
        'color',
        'order'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
