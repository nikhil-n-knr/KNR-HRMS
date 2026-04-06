<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxDeclaration extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'employee_id',
        'tax_section_id',
        'fiscal_year',
        'declared_amount',
        'verified_amount',
        'status',
        'remarks'
    ];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function section() {
        return $this->belongsTo(TaxSection::class, 'tax_section_id');
    }

    public function proofs() {
        return $this->hasMany(InvestmentProof::class);
    }
}
