<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointRule extends Model
{
    use HasFactory;

    protected $fillable = ['event_key', 'event_category', 'name', 'description', 'points', 'condition_logic', 'is_active'];
}
