<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    use HasFactory;

    protected $table = 'crm_user_stats';

    protected $fillable = [
        'tenant_id', 'user_id', 'total_points', 
        'deals_won_count', 'total_revenue', 
        'activities_count', 'current_streak'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
