<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant;
use App\Models\User;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_deals';

    protected $fillable = [
        'tenant_id', 'account_id', 'contact_id', 'title', 'description',
        'value', 'weighted_value', 'currency', 'stage', 'probability', 
        'health_score', 'expected_close_date', 'closed_at', 'status', 
        'loss_reason', 'tags', 'notes', 'ai_insights',
        'created_by', 'assigned_to',
    ];

    protected $casts = [
        'tags' => 'array',
        'ai_insights' => 'array',
        'value' => 'decimal:2',
        'weighted_value' => 'decimal:2',
        'expected_close_date' => 'date',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activityable');
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
    public function isWon()
    {
        return $this->status === 'won';
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }
}
