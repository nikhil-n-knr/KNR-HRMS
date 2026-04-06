<?php

namespace App\Services\ProjectManagement;

use App\Models\Task;
use Carbon\Carbon;

class ScrumMetricService
{
    /**
     * Calculate Automated Priority Score.
     * Formula: (Deadline_Proximity * 0.4) + (Client_Tier * 0.3) + (Dependency_Chain * 0.3)
     * 
     * @param Task $task
     * @return string 'low', 'medium', 'high', 'critical'
     */
    public function calculateAutoPriority(Task $task): string
    {
        $score = 0;

        // 1. Deadline Proximity (0-100)
        // If due in 24h -> 100. Due in 7 days -> 50. Due > 30 days -> 0.
        // Note: Task doesn't have due_date in migration, mapping from Project Deadline or Custom field if added.
        // Assuming Task MIGHT have a due_date or we use Project deadline.
        // Migration check: tasks table has no due_date in the latest snippet, but the Model had `due_date` in casts. 
        // We probably need to add 'due_date' to tasks migration in Phase 6.1 fix, or use Project deadline.
        // For now, let's look at Project Deadline if Task due_date is missing.
        
        $deadline = $task->project->deadline; 
        if ($deadline) {
            $daysUntil = Carbon::now()->diffInDays($deadline, false);
            if ($daysUntil <= 1) $score += (100 * 0.4);
            elseif ($daysUntil <= 7) $score += (50 * 0.4);
            elseif ($daysUntil <= 14) $score += (20 * 0.4);
        }

        // 2. Client Tier (Mock Logic for now)
        // Ideally: $task->project->client->tier
        // Let's assume generic weight for now.
        $score += (50 * 0.3); 

        // 3. Dependencies
        // blocked_by_task_id exists. If this task blocks OTHERS, it's higher priority.
        // We'll check if THIS task is a blocker for others.
        $blockingCount = Task::where('blocked_by_task_id', $task->id)->count();
        if ($blockingCount > 0) {
            $score += (min($blockingCount * 20, 100) * 0.3);
        }

        // Map Score to Enum
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 30) return 'medium';
        return 'low';
    }

    /**
     * Analyze Task Risk based on Velocity Deviation.
     * 
     * @param Task $task
     * @return array [risk_level, message]
     */
    public function analyzeRisk(Task $task): array
    {
        if ($task->estimated_hours <= 0) {
             return ['level' => 'unknown', 'message' => 'No estimate provided.'];
        }

        // Deviation Formula
        $deviation = (($task->actual_hours - $task->estimated_hours) / $task->estimated_hours) * 100;

        if ($deviation > 50) {
             return ['level' => 'critical', 'message' => "Major Overrun: {$deviation}% over estimate."];
        }
        if ($deviation > 20) {
             return ['level' => 'warning', 'message' => "At Risk: {$deviation}% deviation detected."];
        }

        return ['level' => 'safe', 'message' => 'On track.'];
    }
}
