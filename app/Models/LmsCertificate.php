<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsCertificate extends Model
{
    protected $fillable = [
        'employee_id', 'course_id', 'attempt_id', 'certificate_code',
        'issued_on', 'expires_on', 'pdf_path', 'qr_code_path',
        'downloads_count', 'last_downloaded_at'
    ];
    
    protected $casts = [
        'issued_on' => 'date',
        'expires_on' => 'date',
        'last_downloaded_at' => 'datetime',
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }
    
    public function attempt()
    {
        return $this->belongsTo(LmsAttempt::class);
    }
    
    public function incrementDownloads()
    {
        $this->increment('downloads_count');
        $this->update(['last_downloaded_at' => now()]);
    }
    
    public function isExpired(): bool
    {
        return $this->expires_on && now()->gt($this->expires_on);
    }
}
