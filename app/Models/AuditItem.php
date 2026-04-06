<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function session()
    {
        return $this->belongsTo(AuditSession::class, 'audit_session_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
