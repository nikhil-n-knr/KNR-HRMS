<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'criteria_description',
        'icon',
        'points_bonus',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function employeeBadges()
    {
        return $this->hasMany(EmployeeBadge::class);
    }
}
