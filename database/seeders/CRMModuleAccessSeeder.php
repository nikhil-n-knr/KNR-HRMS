<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class CRMModuleAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'crm',
                'display_name' => 'CRM Suite',
                'description' => 'Complete Customer Relationship Management system with contacts, leads, sales pipeline, marketing automation, and customer support.',
                'icon' => 'briefcase',
                'is_premium' => true,
                'is_active' => true,
                'base_price' => 30.00,
                'requires_activation' => true,
                'activation_type' => 'otp',
                'settings' => [
                    'features' => [
                        'contacts', 'accounts', 'leads', 'deals', 
                        'marketing', 'support', 'quotes', 'reports'
                    ],
                    'trial_days' => 14,
                ],
            ],
            [
                'name' => 'advanced_analytics',
                'display_name' => 'Advanced Analytics',
                'description' => 'AI-powered insights, forecasting, and business intelligence dashboards.',
                'icon' => 'chart-bar',
                'is_premium' => true,
                'is_active' => false, // Not yet available
                'base_price' => 20.00,
                'requires_activation' => true,
                'activation_type' => 'license_key',
                'settings' => [
                    'features' => ['ai_insights', 'forecasting', 'custom_dashboards'],
                ],
            ],
            [
                'name' => 'asset_management',
                'display_name' => 'Asset Tracking',
                'description' => 'Hardware, equipment, and asset management with lifecycle tracking.',
                'icon' => 'computer-desktop',
                'is_premium' => true,
                'is_active' => false, // Not yet available
                'base_price' => 10.00,
                'requires_activation' => true,
                'activation_type' => 'otp',
                'settings' => [
                    'features' => ['asset_tracking', 'maintenance', 'depreciation'],
                ],
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                ['name' => $moduleData['name']],
                $moduleData
            );
        }

        $this->command->info('CRM Module Access system seeded successfully!');
    }
}
