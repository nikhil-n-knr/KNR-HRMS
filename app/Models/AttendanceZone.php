<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceZone extends Model
{
    protected $guarded = [];
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
