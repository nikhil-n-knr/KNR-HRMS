<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloatingHolidayAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'total_quota',
        'used_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
