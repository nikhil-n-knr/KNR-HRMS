<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_products';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'supplier_id',
        'created_by',
        'sku',
        'name',
        'type', // physical, service, digital
        'description',
        'base_price',
        'cost_price',
        'currency',
        'tax_code',
        'gl_code',
        'stock_qty',
        'min_order_qty',
        'reorder_point',
        'track_inventory',
        'barcode',
        'dimensions', // json
        'hs_code',
        'warranty_months',
        'is_discontinued',
        'is_active',
        'images', // json
        'video_url', // json
        'custom_fields', // json
        'seo_meta', // json
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_discontinued' => 'boolean',
        'track_inventory' => 'boolean',
        'dimensions' => 'array',
        'images' => 'array',
        'video_url' => 'array',
        'custom_fields' => 'array',
        'seo_meta' => 'array',
    ];

    // Relationships

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\CRM\Account::class, 'supplier_id'); // Assuming Account model can represent suppliers
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function salesHistory()
    {
        return $this->hasMany(ProductSalesHistory::class);
    }

    // Computed Attributes

    public function getProfitMarginAttribute()
    {
        if ($this->base_price > 0) {
            return round((($this->base_price - $this->cost_price) / $this->base_price) * 100, 2);
        }
        return 0;
    }
}
