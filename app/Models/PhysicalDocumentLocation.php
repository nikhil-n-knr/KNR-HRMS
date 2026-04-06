<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalDocumentLocation extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function records() { return $this->hasMany(PhysicalRecord::class, 'location_id'); }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id')->with('children'); }
}
