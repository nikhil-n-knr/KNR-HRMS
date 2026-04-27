<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenanceLog extends Model
{
    use HasFactory;
    protected $table = 'maintenance_logs';
    protected $guarded = [];

    protected $casts = [
        'parts_replaced' => 'array',
        'service_date' => 'date',
        'next_service_date' => 'date'
    ];

    public function asset() { return $this->belongsTo(Asset::class); }
    public function logger() { return $this->belongsTo(User::class, 'logged_by'); }
}
