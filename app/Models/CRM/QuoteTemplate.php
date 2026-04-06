<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'header_html', 'footer_html',
        'terms_template', 'styling', 'is_default', 'is_active'
    ];

    protected $casts = [
        'styling' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'template_id');
    }
}
