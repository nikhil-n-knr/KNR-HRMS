<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTaxRegime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'fiscal_year',
        'regime',
        'locked_at'
    ];
    
    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
