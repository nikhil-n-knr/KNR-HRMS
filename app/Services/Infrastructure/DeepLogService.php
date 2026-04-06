<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DeepLogService
{
    /**
     * Log detailed notification data to a JSON file.
     * Path Structure: storage/logs/notifications/{YYYY-MM-DD}/{channel}/{uuid}.json
     */
    public function log(string $channel, array $data): string
    {
        try {
            $uuid = (string) Str::uuid();
            $date = now()->format('Y-m-d');
            $timestamp = now()->toIso8601String();
            
            // Structure the log content
            $logContent = [
                'id' => $uuid,
                'timestamp' => $timestamp,
                'channel' => $channel,
                'data' => $data
            ];
            
            // Define path. We use the 'local' disk or a custom one. 
            // Saving to storage/logs usually requires accessing the filesystem root or a specific disk.
            // Let's use the 'local' disk which points to storage/app usually, effectively storage path.
            // But strict requirement was "folder under logs". 
            // Ideally we write to storage_path('logs/notifications/...')
            
            $directory = storage_path("logs/notifications/{$date}/{$channel}");
            
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            $filename = "{$uuid}.json";
            $fullPath = "{$directory}/{$filename}";
            
            file_put_contents($fullPath, json_encode($logContent, JSON_PRETTY_PRINT));
            
            return $uuid; // Return Trace ID
            
        } catch (\Exception $e) {
            // Fallback to standard Laravel log if deep logging fails
            Log::error("DeepLogService Failed: " . $e->getMessage());
            return 'formatted-log-failed';
        }
    }
}
