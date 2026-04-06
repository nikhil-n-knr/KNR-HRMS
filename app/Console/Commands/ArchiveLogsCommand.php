<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:archive {--days=30 : Number of days to keep in DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old activity logs to file storage to optimize database performance.';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Infrastructure\LogArchiverService $archiver)
    {
        $days = (int) $this->option('days');
        
        $this->info("Starting archiving for logs older than {$days} days...");
        
        try {
            $count = $archiver->archive($days, 2000); // Process 2000 at a time
            $this->info("Successfully archived {$count} records.");
        } catch (\Exception $e) {
            $this->error("Archiving failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
