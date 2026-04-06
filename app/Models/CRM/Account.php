<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant;
use App\Models\User;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_accounts';

    protected $fillable = [
        'tenant_id', 'name', 'website', 'industry', 'size', 'annual_revenue',
        'phone', 'email', 'fax', 'address', 'city', 'state', 'country',
        'postal_code', 'tags', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'annual_revenue' => 'decimal:2',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activityable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
