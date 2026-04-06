<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'header_html',
        'body_html',
        'footer_html',
        'watermark_text',
        'is_active',
        'layout_config',
        'pages_data',
        'header_image',
        'footer_image',
        'watermark_image',
        'attachments'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'layout_config' => 'array',
        'pages_data' => 'array',
        'attachments' => 'array'
    ];
}
