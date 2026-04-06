<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSubModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'name',
        'key',
        'route',
        'order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(AppModule::class, 'module_id');
    }
}
