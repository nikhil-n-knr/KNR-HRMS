<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'color',
        'days_allowed_per_year',
        'is_paid',
        'requires_document',
        'requires_approval',
        'is_active'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_document' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
