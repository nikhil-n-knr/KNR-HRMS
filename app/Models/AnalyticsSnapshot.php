<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSnapshot extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'snapshot_date' => 'date',
        'annual_ctc' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
