<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceChallan extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'type', // pf, esi, pt
        'amount_paid',
        'transaction_ref',
        'payment_date',
        'document_path',
        'status', // pending, paid
        'metadata'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'metadata' => 'array'
    ];
}
