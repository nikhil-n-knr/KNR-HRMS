<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSection extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'section_code',
        'max_deduction',
        'is_active'
    ];
}
