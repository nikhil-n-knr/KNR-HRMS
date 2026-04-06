<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftRotation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'frequency', 'pattern'];

    protected $casts = [
        'pattern' => 'array',
    ];

    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_rotation')
                    ->withPivot('start_date', 'current_step')
                    ->withTimestamps();
    }
}
