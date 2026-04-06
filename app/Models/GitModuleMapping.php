<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitModuleMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'git_repository_id',
        'project_module_id',
        'app_sub_module_id',
        'path_pattern'
    ];

    public function repository()
    {
        return $this->belongsTo(GitRepository::class, 'git_repository_id');
    }

    public function module()
    {
        return $this->belongsTo(ProjectModule::class, 'project_module_id');
    }

    public function appSubModule()
    {
        return $this->belongsTo(AppSubModule::class, 'app_sub_module_id');
    }
}
