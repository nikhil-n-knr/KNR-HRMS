<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayException extends Model
{
    use HasFactory;

    protected $fillable = ['holiday_id', 'year', 'is_hidden'];
}
