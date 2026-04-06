<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'icon',
        'route',
        'order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function subModules()
    {
        return $this->hasMany(AppSubModule::class, 'module_id');
    }
}
