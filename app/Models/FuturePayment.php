<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuturePayment extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'due_date',
        'status',
        'conditions',
        'payment_frequency'
    ];
    
    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
