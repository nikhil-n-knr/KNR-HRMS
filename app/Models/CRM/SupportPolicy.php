<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportPolicy extends Model
{
    protected $table = 'crm_support_policies';

    protected $fillable = [
        'tenant_id',
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function targets(): HasMany
    {
        return $this->hasMany(SLATarget::class, 'policy_id');
    }
}
