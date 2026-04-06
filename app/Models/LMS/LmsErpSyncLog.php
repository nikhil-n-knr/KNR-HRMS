<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsErpSyncLog extends Model
{
    protected $table = 'lms_erp_sync_logs';
    protected $fillable = [
        'institution_id', 'direction', 'entity_type', 'status',
        'records_processed', 'records_failed', 'error_log', 'triggered_by',
    ];
    protected $casts = ['error_log' => 'array'];
    public function institution() { return $this->belongsTo(LmsInstitution::class); }
}
