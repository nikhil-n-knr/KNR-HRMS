<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'description',
        'price',
        'currency',
        'category',
        'is_active',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
