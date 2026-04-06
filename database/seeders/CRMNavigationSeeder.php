<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CRMNavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if CRM module already exists
        $exists = DB::table('app_modules')
            ->where('key', 'crm')
            ->exists();
        
        if (!$exists) {
            // Get the maximum order for proper positioning
            $maxOrder = DB::table('app_modules')->max('order') ?? 0;
            
            // Insert CRM main module
            $moduleId = DB::table('app_modules')->insertGetId([
                'name' => 'CRM',
                'key' => 'crm',
                'icon' => 'UsersIcon',
                'order' => $maxOrder + 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Insert CRM sub-module (Hub link)
            DB::table('app_sub_modules')->insert([
                'module_id' => $moduleId,
                'name' => 'CRM Hub',
                'key' => 'crm_hub',
                'route' => 'crm.hub',
                'order' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('CRM navigation item added successfully!');
        } else {
            $this->command->info('CRM navigation already exists, skipping...');
        }
    }
}
