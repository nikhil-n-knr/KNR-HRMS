<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceLicence extends Model
{
    protected $fillable = [
        'name',
        'document_path',
        'state_code',
        'location_id',
        'expiry_date',
        'metadata',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'metadata' => 'array',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
