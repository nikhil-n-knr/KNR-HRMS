<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRecommendation extends Model
{
    use HasFactory;

    protected $table = 'crm_customer_recommendations';

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'product_id',
        'score',
        'reason_code',
        'metadata',
    ];

    protected $casts = [
        'score' => 'decimal:4',
        'metadata' => 'array',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
