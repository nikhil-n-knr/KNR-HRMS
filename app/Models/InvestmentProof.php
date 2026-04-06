<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_declaration_id',
        'file_path',
        'file_name'
    ];

    public function declaration() {
        return $this->belongsTo(TaxDeclaration::class, 'tax_declaration_id');
    }
}
