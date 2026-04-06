<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingNote extends Model
{
    use HasFactory;

    protected $table = 'crm_meeting_notes';

    protected $fillable = [
        'meeting_id',
        'summary',
        'decisions',
        'next_steps',
        'follow_up_date',
        'discussed_products', // JSON
    ];

    protected $casts = [
        'discussed_products' => 'json',
        'follow_up_date' => 'date',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }
}
