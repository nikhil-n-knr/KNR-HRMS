<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id', 'product_id', 'name', 'description', 'quantity', 'unit_price',
        'discount', 'tax_rate', 'total', 'order'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\CRM\Product::class);
    }

    protected static function booted()
    {
        static::saved(function ($item) {
            $item->quote->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->quote->calculateTotals();
        });
    }
}
