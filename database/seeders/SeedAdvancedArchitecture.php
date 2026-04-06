<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\PointRule;
use App\Models\Role;

class SeedAdvancedArchitecture extends Seeder
{
    public function run()
    {
        // 1. Find Role 'Manager'
        $managerRole = Role::where('name', 'Manager')->first();
        if (!$managerRole) {
            // Fallback or explicit error - assuming seeded previously.
            // If strictly needed, we'd need tenant context.
            // For now, let's assume it exists or skip assigning explicit ID if unnecessary (the seeder uses 'Manager' as string type).
        }

        // 2. Define Workflows
        $timesheetWorkflow = Workflow::firstOrCreate(
            ['name' => 'Timesheet Approval'],
            ['module' => 'Attendance', 'description' => 'Standard Time Log Approval Flow', 'is_active' => true]
        );

        // 3. Define Stages for Timesheet Workflow
        // Stage 1: Manager Approval (SLA: 24h)
        WorkflowStage::firstOrCreate(
            ['workflow_id' => $timesheetWorkflow->id, 'stage_order' => 1],
            [
                'name' => 'Manager Review',
                'approver_type' => 'Manager', // Dynamic check
                'sla_hours' => 24,
                'escalation_action' => 'Notify',
                'required_approvals' => 1
            ]
        );

        // 4. Define Point Rules
        $rules = [
            ['event_key' => 'timesheet_submission', 'name' => 'Diligent Logger', 'points' => 5, 'description' => 'Submitting timesheet on time'],
            ['event_key' => 'early_bird', 'name' => 'Early Bird', 'points' => 10, 'description' => 'Clocking in before 9:00 AM'],
            ['event_key' => 'late_arrival', 'name' => 'Late Arrival', 'points' => -5, 'description' => 'Clocking in after 9:15 AM'],
            ['event_key' => 'workflow_approved', 'name' => 'Good Citizen', 'points' => 2, 'description' => 'Request approved by manager']
        ];

        foreach ($rules as $r) {
            PointRule::updateOrCreate(
                ['event_key' => $r['event_key']],
                $r
            );
        }

        $this->command->info('Advanced Architecture (Workflows & Gamification) Seeded!');
    }
}
