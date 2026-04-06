<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'title',
        'category',
        'document_type',
        'file_path',
        'file_type',
        'file_size',
        'is_system_generated',
        'source_module',
        'source_reference',
        'metadata',
        'uploaded_by'
    ];

    protected $casts = [
        'is_system_generated' => 'boolean',
        'metadata' => 'array',
    ];

    protected $appends = ['url'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Append Full URL for frontend
    public function getUrlAttribute()
    {
        // Private File Access via ID (Obfuscated Path)
        return route('documents.stream', ['id' => $this->id]);
    }
}
