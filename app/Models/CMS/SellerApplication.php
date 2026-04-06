<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class SellerApplication extends Model
{
    use HasFactory;

    protected $table = 'cms_seller_applications';

    protected $fillable = [
        'tenant_id',
        'name',
        'store_name',
        'email',
        'phone',
        'business_details',
        'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
