<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AssetMaintenanceLog;
use App\Models\Asset;
use App\Models\PurchaseRequest;

class Vendor extends Model
{
    protected $fillable = [
        'name', 'vendor_type', 'contact_person', 'email', 'phone', 'supported_categories', 'service_locations', 'address', 'gstin', 'pan', 'msme_reg', 'tds_rate', 'bank_details', 'category', 'sla_response_hours', 'rating', 'lead_time_days', 'contract_end_date', 'is_active', 'tenant_id'
    ];

    protected $casts = [
        'msme_reg' => 'boolean',
        'bank_details' => 'array',
        'supported_categories' => 'array',
        'service_locations' => 'array',
        'is_active' => 'boolean'
    ];
    
    public function maintenanceLogs()
    {
        // asset_maintenance_logs has no vendor_id; go through assets
        return $this->hasManyThrough(AssetMaintenanceLog::class, Asset::class);
    }
    
    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function itemMappings()
    {
        return $this->hasMany(VendorItemMap::class, 'vendor_id');
    }
}

