<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantSubResource;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory, TenantSubResource, SoftDeletes;

    protected $table = 'cms_sites';

    protected $fillable = [
        'tenant_id', 'name', 'slug', 'domain', 'theme_id',
        'type', 'status', 'is_live',
        'global_settings', 'settings',
        'currency', 'razorpay_key_id', 'razorpay_key_secret',
    ];

    protected $casts = [
        'global_settings' => 'array',
        'settings'        => 'array',
        'is_live'         => 'boolean',
    ];

    protected $hidden = ['razorpay_key_secret'];

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
