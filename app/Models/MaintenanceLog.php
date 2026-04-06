<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
