<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantSubResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Media extends Model
{
    use HasFactory, TenantSubResource, SoftDeletes;

    protected $table = 'cms_media';
    protected $fillable = ['tenant_id', 'file_name', 'file_path', 'mime_type', 'size', 'ai_metadata', 'uploaded_by'];
    protected $casts = [
        'ai_metadata' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
