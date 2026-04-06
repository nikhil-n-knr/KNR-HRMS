<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'expected_date' => 'date'
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
    public function vendor() { return $this->belongsTo(Vendor::class); }
}
