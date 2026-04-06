<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BugTicketView extends Model
{
    protected $fillable = ['user_id', 'name', 'filters'];

    protected $casts = [
        'filters' => 'array',
    ];
}
