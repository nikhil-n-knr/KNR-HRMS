<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'content', 'header_image', 'footer_image', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
