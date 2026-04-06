<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadAssignmentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'criteria',
        'assign_to_user_id',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];
}
