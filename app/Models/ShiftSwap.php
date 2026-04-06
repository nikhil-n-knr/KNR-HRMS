<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSwap extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'recipient_id',
        'shift_id_from',
        'shift_id_to',
        'date',
        'status',
        'approved_by'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Employee::class, 'recipient_id');
    }

    public function shiftFrom()
    {
        return $this->belongsTo(Shift::class, 'shift_id_from');
    }

    public function shiftTo()
    {
        return $this->belongsTo(Shift::class, 'shift_id_to');
    }
}
