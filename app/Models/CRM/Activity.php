<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant;
use App\Models\User;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_activities';

    protected $fillable = [
        'tenant_id', 'type', 'subject', 'description', 'due_date',
        'completed_at', 'is_completed', 'priority', 'activityable_type',
        'activityable_id', 'created_by', 'assigned_to',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function activityable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Helper methods
    public function markAsCompleted()
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function isOverdue()
    {
        return !$this->is_completed && $this->due_date && $this->due_date->isPast();
    }
}
