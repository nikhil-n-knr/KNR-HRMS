<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserScope extends Model
{
    use HasFactory;

    protected $table = 'user_scope';

    protected $fillable = [
        'user_id',
        'permission_id',
        'data_scope',
        'limit_value',
        'reason',
        'valid_until',
        'created_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
