<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhysicalDocumentLocation;

class PhysicalDocumentLocationSeeder extends Seeder
{
    public function run()
    {
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->value('id') ?? 1;

        // Root Locations
        $room1 = PhysicalDocumentLocation::create([
            'tenant_id' => $tenantId, 
            'name' => 'Archive Room A', 
            'type' => 'Room', 
            'access_level' => 1
        ]);
        
        $safe = PhysicalDocumentLocation::create([
            'tenant_id' => $tenantId,
            'name' => 'HR Safe', 
            'type' => 'Safe', 
            'access_level' => 3
        ]);
        
        // Children
        PhysicalDocumentLocation::create([
            'tenant_id' => $tenantId,
            'name' => 'Cabinet 1', 
            'type' => 'Cabinet', 
            'parent_id' => $room1->id,
            'access_level' => 1
        ]);
        
        PhysicalDocumentLocation::create([
            'tenant_id' => $tenantId,
            'name' => 'Cabinet 2', 
            'type' => 'Cabinet', 
            'parent_id' => $room1->id,
            'access_level' => 1
        ]);
        
        PhysicalDocumentLocation::create([
            'tenant_id' => $tenantId,
            'name' => 'Rack 1', 
            'type' => 'Rack', 
            'parent_id' => $room1->id,
            'access_level' => 1
        ]);
        
        // Offsite
        PhysicalDocumentLocation::create([
            'tenant_id' => $tenantId,
            'name' => 'Iron Mountain', 
            'type' => 'Offsite_Storage', 
            'access_level' => 2
        ]);
    }
}
