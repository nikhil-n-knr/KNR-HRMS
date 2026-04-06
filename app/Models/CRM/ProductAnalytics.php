<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAnalytics extends Model
{
    use HasFactory;

    protected $table = 'crm_product_analytics';

    protected $fillable = [
        'tenant_id',
        'metric_key',
        'data',
        'calculated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'calculated_at' => 'datetime',
    ];
}
