<?php

namespace App\Services\CRM;

use App\Models\CRM\Ticket;
use App\Models\CRM\SupportPolicy;
use App\Models\CRM\SlaTarget;
use Carbon\Carbon;

class SLAService
{
    /**
     * Apply SLA policy to a ticket
     */
    public function applySLA(Ticket $ticket)
    {
        $policy = $ticket->sla_policy_id 
            ? SupportPolicy::find($ticket->sla_policy_id)
            : SupportPolicy::where('tenant_id', $ticket->tenant_id)->where('is_default', true)->first();

        if (!$policy) return;

        $target = SlaTarget::where('policy_id', $policy->id)
            ->where('priority', $ticket->priority)
            ->first();

        if (!$target) return;

        $ticket->update([
            'sla_policy_id' => $policy->id,
            'response_due_at' => now()->addMinutes($target->response_time_minutes),
            'resolve_due_at' => now()->addMinutes($target->resolve_time_minutes),
            'sla_status' => 'compliant'
        ]);
    }

    /**
     * Check and update SLA status for a ticket
     */
    public function checkStatus(Ticket $ticket)
    {
        if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
            return;
        }

        $now = now();
        $status = 'compliant';

        if ($ticket->resolve_due_at && $now->gt($ticket->resolve_due_at)) {
            $status = 'resolution_breached';
        } elseif ($ticket->response_due_at && $now->gt($ticket->response_due_at) && !$ticket->first_response_at) {
            $status = 'response_breached';
        }

        if ($ticket->sla_status !== $status) {
            $ticket->update(['sla_status' => $status]);
        }
    }

    /**
     * Record first response
     */
    public function recordFirstResponse(Ticket $ticket)
    {
        if (!$ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
            $this->checkStatus($ticket);
        }
    }

    /**
     * Record resolution
     */
    public function recordResolution(Ticket $ticket)
    {
        if (!$ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
            $this->checkStatus($ticket);
        }
    }
}
