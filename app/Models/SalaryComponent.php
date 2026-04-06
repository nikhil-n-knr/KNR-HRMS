<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    protected $fillable = [
        'salary_structure_id',
        'name',
        'type',
        'calculation_type',
        'value',
        'formula',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function structure()
    {
        return $this->belongsTo(SalaryStructure::class);
    }}
