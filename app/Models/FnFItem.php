<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FnFItem extends Model
{
    use HasFactory;

    protected $table = 'fnf_items';

    protected $fillable = [
        'exit_id',
        'component_name',
        'type', // earning, deduction
        'amount',
        'is_taxable'
    ];

    protected $casts = [
        'is_taxable' => 'boolean'
    ];
}
