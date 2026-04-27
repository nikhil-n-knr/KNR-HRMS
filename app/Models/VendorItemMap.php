<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorItemMap extends Model
{
    use HasFactory;

    protected $table = 'vendor_item_map';

    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
