<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayCalendarStatus extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'year', 'is_published', 'published_at', 'published_by'];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime'
    ];}
