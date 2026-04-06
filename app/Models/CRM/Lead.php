<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant;
use App\Models\User;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_leads';

    protected $fillable = [
        'tenant_id', 'first_name', 'last_name', 'email', 'phone', 'company',
        'title', 'website', 'source', 'status', 'score', 'health_score', 
        'ai_conversion_probability', 'ai_insights', 'city', 'country',
        'converted_at', 'converted_to_contact_id', 'converted_to_account_id',
        'tags', 'notes', 'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'ai_insights' => 'array',
        'converted_at' => 'datetime',
        'ai_conversion_probability' => 'decimal:2',
    ];

    protected $appends = ['full_name'];

    // Accessor
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function convertedToContact()
    {
        return $this->belongsTo(Contact::class, 'converted_to_contact_id');
    }

    public function convertedToAccount()
    {
        return $this->belongsTo(Account::class, 'converted_to_account_id');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activityable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper method
    public function isConverted()
    {
        return $this->status === 'converted' && $this->converted_at !== null;
    }

    protected static function booted()
    {
        static::updated(function ($lead) {
            if ($lead->isDirty('status')) {
                $lead->activities()->create([
                    'tenant_id' => $lead->tenant_id,
                    'type' => 'task',
                    'subject' => "Status updated to: " . ucfirst($lead->status),
                    'description' => "Lead status was changed from {$lead->getOriginal('status')} to {$lead->status}.",
                    'is_completed' => true,
                    'completed_at' => now(),
                    'created_by' => auth()->id() ?? $lead->created_by,
                ]);
            }
        });
    }
}
