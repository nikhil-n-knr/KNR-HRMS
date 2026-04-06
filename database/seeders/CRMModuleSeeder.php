<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CRMModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if CRM module already exists
        $exists = DB::table('modules')->where('name', 'CRM')->exists();
        
        if (!$exists) {
            DB::table('modules')->insert([
                'name' => 'CRM',
                'display_name' => 'Customer Relationship Management',
                'description' => 'Manage leads, contacts, accounts, deals, and activities to drive sales growth and improve customer relationships',
                'icon' => 'users',
                'is_premium' => true,
                'is_active' => true,
                'base_price' => 10.00, // $10 per user/month
                'requires_activation' => true,
                'activation_type' => 'otp',
                'settings' => json_encode([
                    'features' => ['leads', 'contacts', 'accounts', 'deals', 'activities'],
                    'max_users' => null, // Unlimited
                    'trial_days' => 14
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('CRM module seeded successfully!');
        } else {
            $this->command->info('CRM module already exists, skipping...');
        }
    }
}
