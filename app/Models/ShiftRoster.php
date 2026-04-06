<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftRoster extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'is_published',
        'notes',
        'assigned_by'
    ];

    protected $casts = [
        'date' => 'date',
        'is_published' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
