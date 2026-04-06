<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BugAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'file_path',
        'file_type',
        'original_name',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function attachable()
    {
        return $this->morphTo();
    }
}
