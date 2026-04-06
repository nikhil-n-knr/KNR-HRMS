<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LogArchiverService
{
    /**
     * Archive logs older than X days.
     * Moves them to storage/archives/{YYYY}/{MM}/{Entity}/logs.jsonl
     * 
     * @param int $daysOld Threshold (default 30)
     * @param int $chunkSize How many records to process at once to manage memory
     */
    public function archive(int $daysToKeep = 30, int $chunkSize = 1000): int
    {
        $cutoff = now()->subDays($daysToKeep);
        $totalArchived = 0;

        Log::info("Starting Log Archiving for logs older than {$daysToKeep} days.");

        try {
            // Processing in chunks using 'cursor' or simple offset/limit loop with delete
            // Using a loop with IDs is safer for deletion
            
            do {
                // 1. Fetch Chunk
                $logs = DB::table('activity_logs')
                    ->where('created_at', '<', $cutoff)
                    ->orderBy('id')
                    ->limit($chunkSize)
                    ->get();
                
                if ($logs->isEmpty()) {
                    break;
                }

                $idsToDelete = [];
                
                // 2. Process & Sort into Buffers
                // Buffer structure: [ '2024/12/User' => "json_line\njson_line..." ]
                $buffers = [];

                foreach ($logs as $log) {
                    $date = Carbon::parse($log->created_at);
                    $year = $date->format('Y');
                    $month = $date->format('m');
                    
                    // Determine Entity Folder
                    // Use 'System' if no subject, or ClassName "App-Models-User"
                    $entity = 'System';
                    if ($log->subject_type) {
                        $entity = class_basename($log->subject_type);
                    } elseif ($log->log_name) {
                        $entity = Str::studly($log->log_name);
                    }

                    $pathKey = "{$year}/{$month}/{$entity}";

                    if (!isset($buffers[$pathKey])) {
                        $buffers[$pathKey] = "";
                    }

                    $buffers[$pathKey] .= json_encode($log) . "\n";
                    $idsToDelete[] = $log->id;
                }

                // 3. Write Buffers to Files
                foreach ($buffers as $relativePath => $content) {
                    $this->writeToFile($relativePath, $content);
                }

                // 4. Delete From DB
                DB::table('activity_logs')->whereIn('id', $idsToDelete)->delete();
                
                $count = count($idsToDelete);
                $totalArchived += $count;
                
                Log::info("Archived and deleted {$count} logs.");

            } while (true);

        } catch (\Exception $e) {
            Log::error("Log Archiving Failed: " . $e->getMessage());
            throw $e;
        }

        return $totalArchived;
    }

    private function writeToFile(string $relativePath, string $content)
    {
        $base = storage_path('archives');
        $fullPath = "{$base}/{$relativePath}";
        
        // Ensure directory
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // File: logs.jsonl (JSON Lines)
        $file = "{$fullPath}/logs.jsonl";
        
        File::append($file, $content);
    }
}
