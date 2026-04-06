<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantSubResource;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends Model
{
    use HasFactory, TenantSubResource, SoftDeletes;

    protected $table = 'cms_themes';
    protected $fillable = ['tenant_id', 'name', 'colors', 'typography', 'css_framework', 'is_active'];
    protected $casts = [
        'colors' => 'array',
        'typography' => 'array',
        'is_active' => 'boolean',
    ];
}
