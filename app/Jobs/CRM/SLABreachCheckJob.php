<?php

namespace App\Jobs\CRM;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CRM\Ticket;

class SLABreachCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // 1. Check for response breaches
        Ticket::where('status', 'open')
            ->whereNull('first_response_at')
            ->where('response_due_at', '<', now())
            ->where('sla_status', 'compliant')
            ->update(['sla_status' => 'response_breached']);

        // 2. Check for resolution breaches
        Ticket::whereNotIn('status', ['resolved', 'closed'])
            ->where('resolve_due_at', '<', now())
            ->where('sla_status', '!=', 'resolution_breached')
            ->update(['sla_status' => 'resolution_breached']);
    }
}
