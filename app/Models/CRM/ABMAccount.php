<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;
use App\Models\User;

class ABMAccount extends Model
{
    protected $table = 'crm_abm_accounts';

    protected $fillable = [
        'tenant_id',
        'account_id',
        'abm_score',
        'status',
        'journey_map',
        'target_value',
        'assigned_rep_id',
    ];

    protected $casts = [
        'journey_map' => 'array',
        'target_value' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function assignedRep()
    {
        return $this->belongsTo(User::class, 'assigned_rep_id');
    }
}
