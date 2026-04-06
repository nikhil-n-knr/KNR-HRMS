<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class LmsPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'currency',
        'billing_interval', 'features', 'is_active',
    ];

    protected $casts = [
        'features'  => 'array',
        'is_active' => 'boolean',
        'price'     => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($plan) {
            if (!$plan->slug) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions()
    {
        return $this->hasMany(LmsSubscription::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
