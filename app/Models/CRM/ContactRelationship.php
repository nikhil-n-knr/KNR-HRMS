<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class ContactRelationship extends Model
{
    use HasFactory;

    protected $table = 'crm_contact_relationships';

    protected $fillable = [
        'tenant_id', 'contact_id', 'related_contact_id', 
        'relation_type', 'strength', 'notes'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function relatedContact()
    {
        return $this->belongsTo(Contact::class, 'related_contact_id');
    }
}
