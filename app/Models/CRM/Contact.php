<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant;
use App\Models\User;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_contacts';

    protected $fillable = [
        'tenant_id', 'account_id', 'first_name', 'last_name', 'email', 'secondary_email',
        'phone', 'work_phone', 'mobile', 'title', 'department', 
        'linkedin_url', 'twitter_handle', 'social_links', 'date_of_birth',
        'address', 'city', 'state', 'country', 'postal_code', 'tags', 'status',
        'notes', 'created_by', 'influence_score'
    ];

    protected $casts = [
        'tags' => 'array',
        'social_links' => 'array',
        'date_of_birth' => 'date',
    ];

    protected $appends = ['full_name'];

    // Accessor
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships
    public function addresses()
    {
        return $this->hasMany(ContactAddress::class);
    }

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activityable');
    }
    
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
    
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function relationships()
    {
        return $this->hasMany(ContactRelationship::class, 'contact_id');
    }

    public function connections()
    {
        return $this->belongsToMany(Contact::class, 'crm_contact_relationships', 'contact_id', 'related_contact_id')
                    ->withPivot(['relation_type', 'strength', 'notes']);
    }
}
