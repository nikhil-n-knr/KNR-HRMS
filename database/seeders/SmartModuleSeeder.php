<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmartModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Assets Module
        DB::table('app_modules')->updateOrInsert(
            ['key' => 'assets_module'],
            [
                'name' => 'Assets',
                'icon' => 'monitor', 
                'route' => '/admin/assets/dashboard',
                'status' => true,
                'sidebar_group' => 'Resource Management',
                'order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $assetsModuleId = DB::table('app_modules')->where('key', 'assets_module')->value('id');

        // Assets Sub-Modules
        $subModules = [
            ['key' => 'assets_dashboard', 'name' => 'Dashboard', 'route' => '/admin/assets/dashboard'],
            ['key' => 'assets_list', 'name' => 'All Assets', 'route' => '/admin/assets'],
            ['key' => 'assets_requests', 'name' => 'Requests', 'route' => '/admin/asset-requests'],
            ['key' => 'assets_config', 'name' => 'Configuration', 'route' => '/admin/assets/configurations'],
        ];

        foreach ($subModules as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $assetsModuleId, 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. Store (Inventory) Module
        DB::table('app_modules')->updateOrInsert(
            ['key' => 'store_module'],
            [
                'name' => 'Store',
                'icon' => 'archive',
                'route' => '/admin/inventory/dashboard',
                'status' => true,
                'sidebar_group' => 'Resource Management',
                'order' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $storeModuleId = DB::table('app_modules')->where('key', 'store_module')->value('id');

        // Store Sub-Modules
        $storeSubs = [
            ['key' => 'store_dashboard', 'name' => 'Dashboard', 'route' => '/admin/inventory/dashboard'],
            ['key' => 'store_list', 'name' => 'Master List', 'route' => '/admin/inventory'],
            ['key' => 'store_scanner', 'name' => 'Scanner Mode', 'route' => '/admin/inventory/scanner'],
        ];

        foreach ($storeSubs as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $storeModuleId, 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
        // 3. Physical Documents Module (New)
        DB::table('app_modules')->updateOrInsert(
            ['key' => 'documents_module'],
            [
                'name' => 'Documents',
                'icon' => 'folder', // FolderIcon
                'route' => '/admin/physical-documents',
                'status' => true,
                'sidebar_group' => 'Resource Management', // Same Group
                'order' => 52,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $docModuleId = DB::table('app_modules')->where('key', 'documents_module')->value('id');

        // Document Sub-Modules
        $docSubs = [
            ['key' => 'doc_registry', 'name' => 'Registry', 'route' => '/admin/physical-documents'],
            ['key' => 'doc_config', 'name' => 'Storage Maps', 'route' => '/admin/physical-documents/config'],
            ['key' => 'doc_templates', 'name' => 'Templates', 'route' => '/admin/document-templates'],
        ];

        foreach ($docSubs as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $docModuleId, 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // 4. Finance Module (Gap E)
        DB::table('app_modules')->updateOrInsert(
            ['key' => 'finance_module'],
            [
                'name' => 'Finance',
                'icon' => 'currency-dollar', 
                'route' => '/admin/finance/ledger',
                'status' => true,
                'sidebar_group' => 'Resource Management',
                'order' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $financeModuleId = DB::table('app_modules')->where('key', 'finance_module')->value('id');
        $financeSubs = [
             ['key' => 'finance_ledger', 'name' => 'Ledger', 'route' => '/admin/finance/ledger'],
             ['key' => 'finance_procurement', 'name' => 'Procurement', 'route' => '/admin/inventory/procurement/restock'], // Moving Procurement here? Or keep in Store? Let's alias it or keep in Store.
             // Actually, keep Procurement in Store as per Gap D.
        ];
         foreach ($financeSubs as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $financeModuleId, 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // UPDATE EXISTING SUB-MODULES
        
        // Add Maintenance & Audit to Assets
        $newAssetSubs = [
             ['key' => 'assets_maintenance', 'name' => 'Maintenance', 'route' => '/admin/assets/maintenance'], // Gap A
             ['key' => 'assets_audit', 'name' => 'Blind Audit', 'route' => '/admin/assets/audit/run'], // Gap B
        ];
        foreach ($newAssetSubs as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $assetsModuleId, 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
        
        // Add Visual Intelligence
        DB::table('app_sub_modules')->updateOrInsert(
            ['module_id' => $assetsModuleId, 'key' => 'assets_visual'],
            [
                'name' => 'Visual Intelligence',
                'route' => '/admin/analytics/visual',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Add Procurement to Store
        $newStoreSubs = [
             ['key' => 'store_procurement', 'name' => 'Restock Queue', 'route' => '/admin/inventory/procurement/restock'], // Gap D
        ];
        foreach ($newStoreSubs as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $storeModuleId, 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // Add Custody to Documents
        $newDocSubs = [
             ['key' => 'doc_custody', 'name' => 'Chain of Custody', 'route' => '/admin/documents/custody'], // Gap F
        ];
        foreach ($newDocSubs as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $docModuleId, 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
