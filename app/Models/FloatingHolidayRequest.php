<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloatingHolidayRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'holiday_id',
        'status',
        'approved_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }
}
