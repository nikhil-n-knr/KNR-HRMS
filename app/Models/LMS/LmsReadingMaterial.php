<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsReadingMaterial extends Model
{
    protected $table = 'lms_reading_materials';

    protected $fillable = [
        'activity_id', 'content_type', 'content', 'file_path',
        'external_url', 'estimated_read_minutes', 'require_scroll_to_bottom',
    ];

    protected $casts = ['require_scroll_to_bottom' => 'boolean'];
    public function activity() { return $this->belongsTo(LmsActivity::class); }
}
