<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Employee;
use App\Models\Tenant;
use App\Models\Vendor;
use App\Models\Client;
use App\Models\Location;
use App\Models\AssetCategory;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenanceLog;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PurchaseRequest;
use App\Models\PhysicalDocumentLocation;
use App\Models\PhysicalRecord;
use App\Models\AuditSession;
use App\Models\AuditItem;

class ResourceManagementSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        
        $tenant = Tenant::first();
        if (!$tenant) {
            $tenant = Tenant::create(['slug' => 'default', 'name' => 'Default Tenant']);
        }
        $tenantId = $tenant->id;

        $this->command->info('Seeding Locations...');
        // Locations
        $locations = [];
        $locationNames = ['HQ - Server Room', 'HQ - Store Room A', 'Branch - NY Area', 'HQ - Filing Cabinet Room'];
        foreach ($locationNames as $name) {
            $locations[] = Location::firstOrCreate(['name' => $name], ['tenant_id' => $tenantId]);
        }
        
        $this->command->info('Seeding Admin User...');
        $admin = User::firstOrCreate(
            ['email' => 'admin_resource@test.com'],
            ['name' => 'Resource Admin', 'password' => Hash::make('password'), 'tenant_id' => $tenantId]
        );

        $this->command->info('Seeding Users and Employees (50)...');
        $users = [];
        for ($i = 0; $i < 50; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'tenant_id' => $tenantId,
            ]);
            $users[] = $user;

            Employee::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'employee_code' => 'EMP-' . $faker->unique()->numerify('#####'),
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'email' => $user->email,
                'phone' => $faker->phoneNumber,
                'designation' => $faker->jobTitle,
                'joining_date' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                'status' => 'active',
                'employment_type' => 'full_time',
            ]);
        }

        $this->command->info('Seeding Clients (10)...');
        // Clients
        for ($i = 0; $i < 10; $i++) {
            Client::create([
                'name' => $faker->company,
                'code' => strtoupper($faker->bothify('CLI-####')),
                'contact_person' => $faker->name,
                'email' => $faker->companyEmail,
                'portal_access' => $faker->boolean,
                'contract_start' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'contract_end' => $faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            ]);
        }

        $this->command->info('Seeding Vendors (5)...');
        for ($i = 0; $i < 5; $i++) {
            Vendor::create([
                'name' => $faker->company,
                'contact_person' => $faker->name,
                'email' => $faker->companyEmail,
                'phone' => $faker->phoneNumber,
                'is_active' => true,
            ]);
        }

        $this->command->info('Seeding Asset Categories...');
        $categoriesData = [
            ['name' => 'Laptops', 'is_electronic' => true, 'maintenance_interval_days' => 180],
            ['name' => 'Desktops', 'is_electronic' => true, 'maintenance_interval_days' => 365],
            ['name' => 'Office Furniture', 'is_electronic' => false, 'maintenance_interval_days' => null],
            ['name' => 'Company Vehicles', 'is_electronic' => false, 'maintenance_interval_days' => 90],
            ['name' => 'Networking Gear', 'is_electronic' => true, 'maintenance_interval_days' => 365],
        ];
        $categories = [];
        foreach ($categoriesData as $cd) {
            $categories[] = AssetCategory::firstOrCreate(['name' => $cd['name']], $cd);
        }

        $this->command->info('Seeding Assets (100+)...');
        $assets = [];
        $vendors = Vendor::all();
        foreach ($categories as $cat) {
            for ($i = 0; $i < ($cat->name == 'Laptops' ? 50 : 15); $i++) {
                $status = $faker->randomElement(['Available', 'Assigned', 'Assigned', 'In_Service', 'Scrapped']);
                $purchaseCost = $faker->randomFloat(2, 200, 3000);
                $purchaseDate = $faker->dateTimeBetween('-4 years', 'now');
                
                $asset = Asset::create([
                    'tenant_id' => $tenantId,
                    'category_id' => $cat->id,
                    'name' => $cat->name . ' ' . $faker->word,
                    'serial_number' => strtoupper($faker->bothify('SN-####-????')),
                    'status' => $status,
                    'purchase_date' => $purchaseDate->format('Y-m-d'),
                    'warranty_expiry' => (clone $purchaseDate)->modify('+2 years')->format('Y-m-d'),
                    'purchase_cost' => $purchaseCost,
                    'current_value' => max(0, $purchaseCost - 100),
                    'location_id' => $faker->randomElement($locations)->id,
                    'meta' => json_encode(['make' => $faker->word, 'model' => $faker->word]),
                ]);
                $assets[] = $asset;

                // Maintenance logs
                if ($faker->boolean(40)) {
                    AssetMaintenanceLog::create([
                        'asset_id' => $asset->id,
                        'type' => $faker->randomElement(['Repair', 'Routine_Service']),
                        'cost' => $faker->randomFloat(2, 50, 500),
                        'vendor_name' => $vendors->random()->name,
                        'description' => $faker->sentence,
                        'service_date' => $faker->dateTimeBetween($purchaseDate, 'now')->format('Y-m-d'),
                    ]);
                }

                // Assignments
                if ($status == 'Assigned') {
                    AssetAssignment::create([
                        'asset_id' => $asset->id,
                        'user_id' => $faker->randomElement($users)->id,
                        'assigned_by' => $admin->id,
                        'assigned_at' => $faker->dateTimeBetween($purchaseDate, 'now'),
                        'ack_status' => $faker->randomElement(['Accepted', 'Pending']),
                    ]);
                }
            }                                                                                                           
        }

        $this->command->info('Seeding Inventory...');
        $itemNames = ['Printer Paper A4', 'Whiteboard Markers', 'HDMI Cables', 'Wireless Mice', 'Notepads'];
        foreach ($itemNames as $name) {
            $item = InventoryItem::create([
                'tenant_id' => $tenantId,
                'name' => $name,
                'unit' => $faker->randomElement(['Box', 'Each']),
                'current_stock' => $faker->numberBetween(5, 100),
                'min_stock_level' => $faker->numberBetween(10, 20),
            ]);

            // Add transactions
            for ($i = 0; $i < 5; $i++) {
                InventoryTransaction::create([
                    'item_id' => $item->id,
                    'type' => $faker->randomElement(['Purchase', 'Consumption']),
                    'quantity' => $faker->numberBetween(1, 10),
                    'requested_by' => $faker->randomElement($users)->id,
                    'reason' => $faker->sentence,
                ]);
            }
        }

        $this->command->info('Seeding Physical Documents...');
        $pdLoc = PhysicalDocumentLocation::create([
            'tenant_id' => $tenantId,
            'name' => 'HR Safe 1',
            'type' => 'Safe',
            'access_level' => 3
        ]);

        for ($i = 0; $i < 30; $i++) {
            PhysicalRecord::create([
                'user_id' => $faker->randomElement($users)->id,
                'document_type' => $faker->randomElement(['Passport', 'Degree Certificate', 'NDA Contract']),
                'location_id' => $pdLoc->id,
                'container_ref' => 'Box ' . $faker->numberBetween(1, 5),
                'status' => $faker->randomElement(['In_Custody', 'With_Employee']),
                'received_by' => $admin->id,
            ]);
        }

        $this->command->info('Seeding Audits...');
        $session = AuditSession::create([
            'location_id' => $locations[0]->id,
            'auditor_id' => $admin->id,
            'stats' => json_encode(['total_assets' => 10, 'scanned_count' => 9, 'missing_count' => 1, 'accuracy_rate' => 90]),
            'status' => 'Completed',
        ]);

        $auditAssets = Asset::where('location_id', $locations[0]->id)->take(10)->get();
        foreach ($auditAssets as $idx => $asset) {
            AuditItem::create([
                'audit_session_id' => $session->id,
                'asset_id' => $asset->id,
                'status' => $idx == 0 ? 'Missing' : 'Found',
            ]);
        }

        $this->command->info('Resource Management Seeding Complete!');
    }
}
