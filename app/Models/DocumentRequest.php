<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_letter_id',
        'name',
        'is_mandatory',
        'status',
        'rejection_reason',
        'file_path',
        'mime_type',
        'file_size'
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function offerLetter()
    {
        return $this->belongsTo(OfferLetter::class);
    }
}
