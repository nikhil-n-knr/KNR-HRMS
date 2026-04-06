<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dimensions'  => 'array',
        'elements'    => 'array',
        'is_active'   => 'boolean',
        'is_default'  => 'boolean',
        'design_data' => 'array', // Fabric.js JSON — stored as longText, cast as array
    ];

    /**
     * Get the full public URL for the preview image.
     */
    public function getPreviewUrlAttribute(): ?string
    {
        if (!$this->preview_image) return null;
        return asset('storage/' . $this->preview_image);
    }

    protected $appends = ['preview_url'];
}
