<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function passes()
    {
        return $this->hasMany(VisitorPass::class);
    }
    
    // Check if currently inside
    public function getIsCheckedInAttribute()
    {
        return $this->passes()
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->exists();
    }
}
