<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class ProductImportHistory extends Model
{
    protected $table = 'crm_product_import_history';

    protected $fillable = [
        'tenant_id',
        'file_name',
        'status',
        'total_rows',
        'ai_insights',
        'mapping_data',
    ];

    protected $casts = [
        'ai_insights' => 'array',
        'mapping_data' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
