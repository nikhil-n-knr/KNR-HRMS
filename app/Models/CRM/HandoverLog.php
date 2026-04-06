<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandoverLog extends Model
{
    use HasFactory;

    protected $table = 'crm_handover_logs';

    protected $fillable = [
        'tenant_id',
        'from_user_id',
        'to_user_id',
        'entity_type',
        'entity_ids',
        'transfer_options',
        'completed_at',
    ];

    protected $casts = [
        'entity_ids' => 'json',
        'transfer_options' => 'json',
        'completed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
