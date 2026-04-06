<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompOffCredit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_earned' => 'date',
        'expiry_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
