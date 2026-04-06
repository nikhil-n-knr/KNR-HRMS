<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    use HasFactory;

    protected $table = 'crm_user_achievements';

    protected $fillable = [
        'user_id', 'achievement_id', 'earned_at'
    ];

    protected $casts = [
        'earned_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }
}
