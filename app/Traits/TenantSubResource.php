<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait TenantSubResource
{
    /**
     * Boot the trait for a model.
     *
     * @return void
     */
    protected static function bootTenantSubResource()
    {
        if (auth()->check() && auth()->user()->tenant_id) {
            static::addGlobalScope('tenant', function (Builder $builder) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', auth()->user()->tenant_id);
            });
        }
    }

    /**
     * Get the tenant that owns the resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
