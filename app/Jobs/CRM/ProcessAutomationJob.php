<?php

namespace App\Jobs\CRM;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CRM\AutomationExecution;
use App\Services\CRM\AutomationEngineService;

class ProcessAutomationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AutomationEngineService $service): void
    {
        $executions = AutomationExecution::where('status', 'active')
            ->where('next_execution_at', '<=', now())
            ->get();

        foreach ($executions as $execution) {
            $service->processNextStep($execution);
        }
    }
}
