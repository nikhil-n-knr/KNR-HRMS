<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetStoreData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:reset-data {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncates actual asset and inventory records while retaining configuration (categories, definitions).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('⚠️  WARNING: This will DESTROY all transactional Store & Asset data (assets, logs, requests, inventory records).');
        
        if (!$this->option('force')) {
            if (!$this->confirm('Do you wish to proceed and empty the actual store data?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        // Ordered array: Safe to truncate because we disable FK checks anyway, 
        // but intellectually grouped for clarity.
        $tablesToTruncate = [
            'physical_records',
            'inventory_transactions',
            'inventory_items',
            'purchase_requests',
            'asset_attribute_values',
            'asset_requests',
            'asset_maintenance_logs',
            'asset_assignments',
            'assets'
        ];

        $this->info('Disabling foreign key constraints...');
        
        // Disable Foreign Key checks to prevent truncation failures
        // resulting from linked histories and assignment records
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $bar = $this->output->createProgressBar(count($tablesToTruncate));
        $bar->start();

        foreach ($tablesToTruncate as $table) {
            try {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            } catch (\Exception $e) {
                // If the table doesn't exist or fails, log it and move on
                \Log::error("Failed to truncate {$table}: " . $e->getMessage());
                $this->error("\nFailed to truncate {$table}: " . $e->getMessage());
            }
            $bar->advance();
        }

        // Extremely important: Re-enable foreign key checks immediately!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bar->finish();
        $this->newLine(2);
        
        $this->info('✅ Store and Asset transactional data emptied successfully!');
        $this->line('Kept Categories, Definitions, and components structurally intact so you can start cleanly loading live records.');
    }
}
