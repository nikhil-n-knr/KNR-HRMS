<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsCertificateV2 extends Model
{
    protected $table = 'lms_certificates_v2';

    protected $fillable = [
        'user_id', 'rule_id', 'template_id', 'course_id', 'program_id', 'module_id',
        'type', 'unique_code', 'qr_data', 'pdf_path', 'metadata',
        'issued_at', 'is_revoked', 'revoked_at', 'revocation_reason',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'issued_at'   => 'datetime',
        'revoked_at'  => 'datetime',
        'is_revoked'  => 'boolean',
    ];

    public function user()    { return $this->belongsTo(\App\Models\User::class); }
    public function rule()    { return $this->belongsTo(LmsCertificateRule::class); }
    public function course()  { return $this->belongsTo(\App\Models\LmsCourse::class); }
    public function program() { return $this->belongsTo(LmsProgram::class); }
}
