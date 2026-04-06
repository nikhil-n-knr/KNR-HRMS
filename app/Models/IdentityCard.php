<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityCard extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $casts = [
        'details' => 'array',
        'issue_date' => 'date',
        'valid_until' => 'date'
    ];

    public function template()
    {
        return $this->belongsTo(CardTemplate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Helper to generate unique Card Number
    public static function generateCardNumber()
    {
        return 'ID-' . date('Y') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
    }
}
