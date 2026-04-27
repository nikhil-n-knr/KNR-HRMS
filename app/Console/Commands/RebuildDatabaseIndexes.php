<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RebuildDatabaseIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:rebuild-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimizes and rebuilds indexes on all database tables.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing Database Index Rebuild/Optimization...');

        try {
            // Get all tables depending on Laravel Driver/Version schema standards
            $tables = Schema::getTables();
            
            if (empty($tables)) {
                $this->warn('No tables found in the database.');
                return;
            }

            $this->info('Found ' . count($tables) . ' tables. Starting optimization...');
            
            $bar = $this->output->createProgressBar(count($tables));
            $bar->start();

            foreach ($tables as $table) {
                // Laravel 11 Schema::getTables() returns array of arrays with 'name' key setup
                $tableName = is_array($table) ? $table['name'] : (is_object($table) ? $table->name : $table);
                
                try {
                    // OPTIMIZE TABLE works inherently in MySQL to rebuild indexes and reclaim unused data space
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                } catch (\Exception $e) {
                    // Fail gracefully on Views or tables without OPTIMIZE support
                    \Log::warning("Failed to optimize table: {$tableName}", ['error' => $e->getMessage()]);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            
            $this->info('✅ Database indexes optimized and rebuilt successfully!');
            
        } catch (\Exception $e) {
             $this->error('Failed to rebuild indexes. Error: ' . $e->getMessage());
        }
    }
}
