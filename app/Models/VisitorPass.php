<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorPass extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'badge_collected_at' => 'datetime',
        'meta_data' => 'array',
        'expected_duration' => 'integer',
        'actual_duration' => 'integer',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
