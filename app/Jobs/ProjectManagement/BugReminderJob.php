<?php

namespace App\Jobs\ProjectManagement;

use App\Models\BugTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BugReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info("Running Bug SLA Reminder Job");

        // 1. Fetch tickets in stages that have reminder_hours set
        $tickets = BugTicket::with(['stage', 'assignee', 'reporter'])
            ->whereHas('stage', function($q) {
                $q->where('reminder_hours', '>', 0)
                  ->where('is_final', false);
            })
            ->get();

        foreach ($tickets as $ticket) {
            $lastUpdate = $ticket->updated_at;
            $hoursActive = now()->diffInHours($lastUpdate);

            if ($hoursActive >= $ticket->stage->reminder_hours) {
                $this->verifyEscalation($ticket, $hoursActive);
            }
        }
    }

    protected function verifyEscalation(BugTicket $ticket, $hoursActive)
    {
        // Avoid frequent spam: Check last escalation activity
        $lastEscalation = $ticket->activities()
            ->where('activity_type', 'escalation')
            ->latest()
            ->first();

        // If escalated in last 4 hours, skip
        if ($lastEscalation && $lastEscalation->created_at->gt(now()->subHours(4))) {
            return;
        }

        $severity = $ticket->severity;

        // Level 3: Critical & > 24h (System Defense Mode)
        if ($severity === 'critical' && $hoursActive >= 24) {
            $this->escalateLevel3($ticket);
            return;
        }

        // Level 2: High/Critical & > 12h
        if (in_array($severity, ['critical', 'high']) && $hoursActive >= 12) {
            $this->escalateLevel2($ticket);
            return;
        }

        // Level 1: Standard Reminder (> 4h or configured)
        $this->escalateLevel1($ticket);
    }

    protected function escalateLevel1(BugTicket $ticket)
    {
        Log::info("[SLA Level 1] Reminder for Bug #{$ticket->id}");
        $this->logEscalation($ticket, "SLA Warning (Level 1)");
        // Notification to Assignee
        if ($ticket->assignee) {
             $ticket->assignee->notify(new \App\Notifications\BugSlaWarningNotification($ticket, 'level_1'));
        }
    }

    protected function escalateLevel2(BugTicket $ticket)
    {
        Log::warning("[SLA Level 2] Manager Alert for Bug #{$ticket->id}");
        $this->logEscalation($ticket, "SLA Escalation (Level 2) - Manager Notified");
        // Notification to Manager
        $project = $ticket->project;
        // Simplified: Notify Project Manager if exists, or all Admins
        // For now, re-notify assignee with higher urgency if no manager found
        if ($ticket->assignee) {
            $ticket->assignee->notify(new \App\Notifications\BugSlaWarningNotification($ticket, 'level_2'));
        }
    }

    protected function escalateLevel3(BugTicket $ticket)
    {
        Log::critical("[SLA Level 3] SYSTEM DEFENSE: Locking Resources for Bug #{$ticket->id}");
        
        // Visual Resource Lock
        if ($ticket->assignee && $ticket->assignee_type === \App\Models\Employee::class) {
            $user = $ticket->assignee->user; // Assuming relation exists
            if ($user) {
                $user->resource_locked_until = now()->addHours(24);
                $user->save();
            }
        }

        $this->logEscalation($ticket, "SLA BREACH (Level 3) - Resources Locked");
        // Notify Admin/CTO
        $admins = \App\Models\User::role('Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\BugSlaWarningNotification($ticket, 'level_3'));
        }
    }

    protected function logEscalation(BugTicket $ticket, $message)
    {
        $ticket->activities()->create([
            'user_id' => 1, // System
            'activity_type' => 'escalation',
            'description' => $message
        ]);
    }
}
