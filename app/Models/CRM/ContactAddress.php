<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactAddress extends Model
{
    use HasFactory;

    protected $table = 'crm_contact_addresses';

    protected $fillable = [
        'contact_id', 'type', 'street', 'city', 'state', 'country', 'postal_code', 'is_primary'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
