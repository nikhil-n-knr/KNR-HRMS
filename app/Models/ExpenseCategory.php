<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 'code', 'icon', 'description', 'workflow_id', 
        'requires_bill_proof', 'default_payout_method', 'is_active', 'sort_order',
        'unit_type', 'unit_rate',
        'limits', 'rules', 'visibility'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_bill_proof' => 'boolean',
        'limits' => 'array',
        'rules' => 'array',
        'visibility' => 'array'
    ];
    
    public function workflow() {
        return $this->belongsTo(Workflow::class);
    }
}
