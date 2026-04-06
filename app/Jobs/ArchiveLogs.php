<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ArchiveLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $retentionDays = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(int $retentionDays = 30)
    {
        $this->retentionDays = $retentionDays;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $logPath = storage_path('logs');
        $archivePath = storage_path('archives');

        if (!File::exists($logPath)) {
            return;
        }

        // Iterate through module directories
        $directories = File::directories($logPath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $files = File::files($directory);

            foreach ($files as $file) {
                // Filename expected: YYYY-MM-DD.log
                $filename = $file->getFilename();
                $datePart = str_replace('.log', '', $filename);
                
                // Validate date format slightly
                if (strtotime($datePart) === false) {
                    continue; // Skip non-date files like laravel.log
                }

                if (now()->parse($datePart)->addDays($this->retentionDays)->isPast()) {
                    // It's old. Move it.
                    $yearMonth = substr($datePart, 0, 7); // YYYY-MM
                    $targetDir = "{$archivePath}/{$moduleName}/{$yearMonth}";
                    
                    if (!File::exists($targetDir)) {
                        File::makeDirectory($targetDir, 0755, true);
                    }

                    File::move($file->getPathname(), "{$targetDir}/{$filename}");
                }
            }
        }
        
        Log::info("Logs archived successfully.");
    }
}
