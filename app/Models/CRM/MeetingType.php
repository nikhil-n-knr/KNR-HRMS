<?php

namespace App\Models\CRM;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingType extends Model
{
    use HasFactory;

    protected $table = 'crm_meeting_types';

    protected $fillable = [
        'tenant_id',
        'name',
        'duration_minutes',
        'buffer_minutes',
        'reminder_ladder',
        'default_template_id',
    ];

    protected $casts = [
        'reminder_ladder' => 'json',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}
