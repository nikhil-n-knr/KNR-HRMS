<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_letter_id',
        'version_number',
        'payload',
        'created_by'
    ];

    protected $casts = [
        'payload' => 'array'
    ];

    public function offerLetter()
    {
        return $this->belongsTo(OfferLetter::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
