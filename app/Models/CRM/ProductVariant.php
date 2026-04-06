<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'crm_product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price_adjustment',
        'stock_qty',
        'attributes', // json
    ];

    protected $casts = [
        'attributes' => 'array',
        'price_adjustment' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
