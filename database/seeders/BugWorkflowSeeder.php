<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BugWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Parent Workflow
        $workflow = \App\Models\Workflow::firstOrCreate(
            ['name' => 'Bug Tracking Workflow'],
            [
                'description' => 'Standard workflow for bug tracking lifecycle',
                'entity_type' => 'App\Models\BugTicket', // Polymorphic link
                'trigger_event' => 'manual',
                'is_active' => true,
                'priority' => 1
            ]
        );

        // Ensure a department exists
        $dept = \App\Models\Department::firstOrCreate(['name' => 'Engineering'], ['code' => 'ENG', 'description' => 'Engineering Department']);

        $stages = [
            [
                'name' => 'Triage',
                'stage_order' => 1,
                'approver_type' => 'department_head', // Default placeholder
                'department_id' => $dept->id,
                'is_client_visible' => false,
                'reminder_hours' => 4,
                'is_final' => false,
                'notify_incharge' => true
            ],
            [
                'name' => 'In Development',
                'stage_order' => 2,
                'approver_type' => 'role', // Dev
                'department_id' => $dept->id,
                'is_client_visible' => false,
                'reminder_hours' => 48,
                'is_final' => false,
                'notify_incharge' => false
            ],
            [
                'name' => 'QA',
                'stage_order' => 3,
                'approver_type' => 'role', // QA
                'department_id' => $dept->id,
                'is_client_visible' => false,
                'reminder_hours' => 24,
                'is_final' => false,
                'notify_incharge' => false
            ],
            [
                'name' => 'Client Review',
                'stage_order' => 4,
                'approver_type' => 'specific_user', // Client
                'department_id' => $dept->id,
                'is_client_visible' => true,
                'reminder_hours' => 72,
                'is_final' => false,
                'notify_incharge' => true
            ],
            [
                'name' => 'Closed',
                'stage_order' => 5,
                'approver_type' => 'department_head',
                'department_id' => $dept->id,
                'is_client_visible' => true,
                'reminder_hours' => null,
                'is_final' => true,
                'notify_incharge' => false
            ]
        ];

        foreach ($stages as $stage) {
            \App\Models\WorkflowStage::updateOrCreate(
                [
                    'workflow_id' => $workflow->id,
                    'name' => $stage['name']
                ],
                $stage
            );
        }
    }

}
