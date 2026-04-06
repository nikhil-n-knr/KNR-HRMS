<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class CrmCustomField extends Model
{
    protected $fillable = [
        'tenant_id',
        'entity_type',
        'label',
        'name',
        'type',
        'options',
        'is_required',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
