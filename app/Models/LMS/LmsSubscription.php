<?php

namespace App\Models\LMS;

use App\Models\User;
use App\Models\LmsCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LmsSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'plan_id', 'course_id', 'status',
        'starts_at', 'ends_at', 'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'metadata'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(LmsPlan::class, 'plan_id');
    }

    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    public function transactions()
    {
        return $this->hasMany(LmsTransaction::class, 'subscription_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }
}
