<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // header, footer
        'content', // HTML
        'settings', // JSON config
        'image_path',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];
}
