<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Core\ModuleService;

class SyncModuleRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:sync-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs the route paths for app modules and sub-modules in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Route Synchronization...');

        // Master Map of Key -> Frontend Route
        // Format: 'module_key.submodule_key' => '/route'
        // Or just 'submodule_key' if unique enough
        $routes = [
            // User Management
            'user' => '/users',
            'role' => '/roles',
            'permission' => '/roles/matrix',
            'scope' => '/roles/matrix',
            'access_review' => '/users/access-review',

            // Organization
            'departments' => '/organization/departments',
            'locations' => '/organization/locations',
            'tenants' => '/organization/tenants',

            // HR Core (Placeholders for future)
            'employees' => '/employees',
            'attendance' => '/attendance',
            'payroll' => '/payroll',
        ];

        $count = 0;

        foreach ($routes as $key => $path) {
            $updated = DB::table('app_sub_modules')
                ->where('key', $key)
                ->update(['route' => $path]);
            
            if ($updated) {
                $this->line("Synced: <info>$key</info> -> <comment>$path</comment>");
                $count++;
            } else {
                // Try linking via module?? No, key should be sufficient usually
                // $this->warn("Skipped: $key (Not found)");
            }
        }

        // Also sync main modules if they have dashboard links
        DB::table('app_modules')->where('key', 'dashboard')->update(['route' => '/']);

        // Clear Cache
        (new ModuleService)->clearCache();

        $this->info("Sync Complete. Updated $count routes.");
        $this->info("Module Cache Cleared.");
    }
}
