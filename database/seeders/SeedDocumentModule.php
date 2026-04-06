<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedDocumentModule extends Seeder
{
    public function run()
    {
        $employeeModuleId = DB::table('app_modules')->where('key', 'employee_management')->value('id');

        if ($employeeModuleId) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['key' => 'documents', 'module_id' => $employeeModuleId],
                [
                    'name' => 'Documents / DMS',
                    'route' => null, // Tab based
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            // Add Permissions
            $permissions = [
                'employee_management.documents.view',
                'employee_management.documents.create',
                'employee_management.documents.delete',
            ];

            foreach ($permissions as $perm) {
                DB::table('permissions')->updateOrInsert(
                    ['key' => $perm],
                    ['module_id' => $employeeModuleId, 'name' => 'Manage Documents']
                );
            }
        }
    }
}
