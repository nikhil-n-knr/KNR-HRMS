<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanInterestRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_product_id', 
        'min_amount', 'max_amount', 
        'min_tenure_months', 'max_tenure_months',
        'interest_rate'
    ];

    public function product() {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }
}
