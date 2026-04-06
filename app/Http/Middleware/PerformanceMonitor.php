<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Safety: If PDO is missing (server Config error), skip to avoid crash
        if (!extension_loaded('pdo')) {
            return $next($request);
        }

        // 2. Performance: Only enable Query Log in Debug mode or if explicitly requested
        // In Production, we usually don't want to log every query for every request due to memory overhead.
        $loggingEnabled = config('app.debug'); 

        if ($loggingEnabled) {
            DB::enableQueryLog();
        }
        
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $queryCount = $loggingEnabled ? count(DB::getQueryLog()) : 0;
        
        // Log slow requests (> 1000ms) or high query counts (> 50)
        // Reduced sensitivity for Production
        if ($duration > 1000 || $queryCount > 50) {
            // Context logging
            $context = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration_ms' => $duration,
                'user_id' => auth()->id(),
            ];
            
            if ($loggingEnabled) {
                $context['queries'] = $queryCount;
            }

            Log::warning('Performance Alert', $context);
        }

        return $response;
    }
}
