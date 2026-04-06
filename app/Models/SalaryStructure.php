<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    protected $fillable = ['name', 'breakdown', 'description', 'is_default', 'tds_method', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'breakdown' => 'array'
    ];

    public function components()
    {
        return $this->hasMany(SalaryComponent::class)->orderBy('order');
    }
}
