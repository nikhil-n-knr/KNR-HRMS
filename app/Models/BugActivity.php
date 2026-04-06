<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BugActivity extends Model
{
    use HasFactory;

    protected $fillable = ['bug_ticket_id', 'actor_id', 'actor_type', 'activity_type', 'description', 'details'];
    
    protected $casts = [
        'details' => 'array'
    ];

    public function bug()
    {
        return $this->belongsTo(BugTicket::class, 'bug_ticket_id');
    }

    public function actor()
    {
        return $this->morphTo();
    }

    // Maintained for backward compatibility or when you specifically want a User
    public function user()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
