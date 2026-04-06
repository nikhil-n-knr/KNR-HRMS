<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'rule_id',
        'points',
        'event_key',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rule()
    {
        return $this->belongsTo(PointRule::class, 'rule_id');
    }
}
