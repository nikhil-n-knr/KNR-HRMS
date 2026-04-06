<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PageVersion extends Model
{
    use HasFactory;

    protected $table = 'cms_page_versions';

    protected $fillable = ['page_id', 'tenant_id', 'layout_data', 'created_by', 'commit_message'];

    protected $casts = [
        'layout_data' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function saver()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Alias used by controllers
    public function savedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
