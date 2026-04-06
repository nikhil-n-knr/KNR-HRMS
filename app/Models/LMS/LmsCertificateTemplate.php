<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsCertificateTemplate extends Model
{
    protected $table = 'lms_certificate_templates';

    protected $fillable = [
        'name', 'type', 'institution_id', 'html_template',
        'dynamic_fields', 'background_image', 'signature_image',
        'logo_image', 'layout_config', 'is_default', 'is_active', 'created_by',
    ];

    protected $casts = [
        'dynamic_fields' => 'array',
        'layout_config'  => 'array',
        'is_default'     => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function institution() { return $this->belongsTo(LmsInstitution::class); }
    public function createdBy()   { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
}
