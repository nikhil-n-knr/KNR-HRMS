<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'record_count',
        'success_count',
        'fail_count',
        'errors',
        'status',
        'uploaded_by'
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
