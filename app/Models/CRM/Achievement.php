<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $table = 'crm_achievements';

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 
        'points_reward', 'requirement_type', 'requirement_value'
    ];

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class, 'achievement_id');
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'crm_user_achievements', 'achievement_id', 'user_id')
                    ->withPivot('earned_at');
    }
}
