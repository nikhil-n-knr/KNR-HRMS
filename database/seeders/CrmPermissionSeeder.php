<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class CrmPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Leads
            ['module' => 'CRM', 'submodule' => 'Leads', 'action' => 'view_all', 'description' => 'View all leads across tenant'],
            ['module' => 'CRM', 'submodule' => 'Leads', 'action' => 'create', 'description' => 'Create new leads'],
            ['module' => 'CRM', 'submodule' => 'Leads', 'action' => 'edit', 'description' => 'Edit lead information'],
            ['module' => 'CRM', 'submodule' => 'Leads', 'action' => 'delete', 'description' => 'Delete lead records'],
            ['module' => 'CRM', 'submodule' => 'Leads', 'action' => 'convert', 'description' => 'Convert leads to contacts/deals'],
            
            // Deals
            ['module' => 'CRM', 'submodule' => 'Deals', 'action' => 'view_all', 'description' => 'View all sales opportunities'],
            ['module' => 'CRM', 'submodule' => 'Deals', 'action' => 'create', 'description' => 'Initialize new deals'],
            ['module' => 'CRM', 'submodule' => 'Deals', 'action' => 'edit', 'description' => 'Update deal progress and value'],
            ['module' => 'CRM', 'submodule' => 'Deals', 'action' => 'delete', 'description' => 'Remove deal records'],
            ['module' => 'CRM', 'submodule' => 'Deals', 'action' => 'forecast', 'description' => 'Access and modify sales forecasts'],

            // Contacts & Accounts
            ['module' => 'CRM', 'submodule' => 'Contacts', 'action' => 'view_all', 'description' => 'View all customer profiles'],
            ['module' => 'CRM', 'submodule' => 'Contacts', 'action' => 'manage', 'description' => 'Create and edit contacts/accounts'],
            
            // Support
            ['module' => 'CRM', 'submodule' => 'Support', 'action' => 'view_all_tickets', 'description' => 'Access all customer support tickets'],
            ['module' => 'CRM', 'submodule' => 'Support', 'action' => 'manage_kb', 'description' => 'Create and edit knowledge base articles'],

            // Configuration
            ['module' => 'CRM', 'submodule' => 'Config', 'action' => 'manage_global', 'description' => 'Modify CRM system settings, products and pipelines'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(
                ['module' => $p['module'], 'submodule' => $p['submodule'], 'action' => $p['action']],
                ['description' => $p['description']]
            );
        }
    }
}
