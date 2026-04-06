<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantSubResource;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, TenantSubResource, SoftDeletes;

    protected $table = 'cms_pages';
    protected $fillable = ['tenant_id', 'site_id', 'title', 'slug', 'seo_meta', 'layout_data', 'status', 'priority'];
    protected $casts = [
        'seo_meta' => 'array',
        'layout_data' => 'array',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function versions()
    {
        return $this->hasMany(PageVersion::class);
    }
}
