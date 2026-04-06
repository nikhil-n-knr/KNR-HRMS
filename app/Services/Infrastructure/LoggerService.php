<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LoggerService
{
    /**
     * Log a message to a specific module's daily log file in JSON format.
     *
     * @param string $module The module name (e.g., 'payroll', 'user_management')
     * @param string $action The action performed (e.g., 'create', 'update')
     * @param string $message A descriptive message
     * @param array $context Additional context data
     * @param string $level Log level (info, warning, error)
     * @return void
     */
    public function log(string $module, string $action, string $message, array $context = [], string $level = 'info'): void
    {
        $date = now()->format('Y-m-d');
        $logPath = "logs/{$module}/{$date}.log";
        
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'module' => $module,
            'action' => $action,
            'level' => $level,
            'message' => $message,
            'user_id' => auth()->id() ?? 'system',
            'ip' => request()->ip(),
            'context' => $context,
        ];

        $jsonLine = json_encode($logData);

        $fullPath = storage_path($logPath);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($fullPath, $jsonLine . PHP_EOL, FILE_APPEND);
    }

    /**
     * Helper methods for standardized logging
     */
    public function logInfo(string $message, array $context = [], string $module = 'system'): void
    {
        $this->log($module, 'INFO', $message, $context, 'info');
    }

    public function logWarning(string $message, array $context = [], string $module = 'system'): void
    {
        $this->log($module, 'WARNING', $message, $context, 'warning');
    }

    public function logError(string $message, array $context = [], string $module = 'system'): void
    {
        $this->log($module, 'ERROR', $message, $context, 'error');
    }
}
