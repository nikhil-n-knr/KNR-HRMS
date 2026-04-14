<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'module_id',
        'uploader_id',
        'uploader_type',
        'name',
        'description',
        'category',
        'file_path',
        'mime_type',
        'file_size',
        'visibility',
        'shared_with',
        'shared_with_teams',
        'is_signed',
        'signed_at',
        'signed_by',
        'version_number',
        'parent_id',
        'is_current'
    ];

    protected $casts = [
        'shared_with' => 'array',
        'shared_with_teams' => 'array',
        'file_size' => 'integer',
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
        'is_current' => 'boolean',
        'version_number' => 'integer',
        'uploader_type' => 'string'
    ];

    public function parent()
    {
        return $this->belongsTo(ProjectDocument::class, 'parent_id');
    }

    public function history()
    {
        return $this->hasMany(ProjectDocument::class, 'parent_id')->orderBy('version_number', 'desc');
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function module()
    {
        return $this->belongsTo(ProjectModule::class);
    }

    public function uploader()
    {
        return $this->morphTo('uploader', 'uploader_type', 'uploader_id');
    }
    
    /**
     * Accessor for Human Readable Size
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }
}
