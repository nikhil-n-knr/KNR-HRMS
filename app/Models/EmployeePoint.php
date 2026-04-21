<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EmployeePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'rule_id',
        'point_rule_id',
        'points',
        'points_awarded',
        'event_key',
        'event_reference',
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
        $foreignKey = Schema::hasColumn($this->getTable(), 'rule_id') ? 'rule_id' : 'point_rule_id';
        return $this->belongsTo(PointRule::class, $foreignKey);
    }
}
