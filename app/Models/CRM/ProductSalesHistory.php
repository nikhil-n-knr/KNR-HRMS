<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSalesHistory extends Model
{
    use HasFactory;

    protected $table = 'crm_product_sales_history';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'period',
        'units_sold',
        'revenue',
        'unique_customers',
        'recurring_flag',
    ];

    protected $casts = [
        'period' => 'date',
        'recurring_flag' => 'boolean',
        'revenue' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
