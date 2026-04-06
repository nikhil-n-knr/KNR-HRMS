<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    use HasFactory;

    protected $table = 'crm_email_accounts';

    protected $fillable = [
        'tenant_id',
        'email_address',
        'provider',
        'credentials',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function getCredentialsAttribute($value)
    {
        try {
            return $value ? json_decode(decrypt($value), true) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = encrypt(json_encode($value));
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'crm_email_account_user')->withTimestamps();
    }

    public function threads()
    {
        return $this->hasMany(EmailThread::class, 'email_account_id');
    }
}
