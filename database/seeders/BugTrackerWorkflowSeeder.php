<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\BugTicket;

class BugTrackerWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Aggressive Cleanup: Purge all existing workflows for BugTicket to avoid "Ghost Stages"
        // This fixes the issue where unrelated workflows (like WFH) were incorrectly linked to BugTicket
        Workflow::where('entity_type', BugTicket::class)->delete();
        Workflow::where('name', 'like', '%Bug Tracking%')->delete();
        
        // Ensure no orphaned stages for this entity type remain
        WorkflowStage::whereHas('workflow', function($q) {
            $q->where('entity_type', BugTicket::class);
        })->delete();

        // 2. Create the Final Primary Workflow
        $workflow = Workflow::create([
            'entity_type' => BugTicket::class,
            'name' => 'Bug Tracking Pipeline'
        ]);

        $stages = [
            [
                'name' => 'Triage',
                'order' => 1,
                'color' => '#64748b', // Slate
            ],
            [
                'name' => 'Open',
                'order' => 2,
                'color' => '#3b82f6', // Blue
            ],
            [
                'name' => 'In Development',
                'order' => 3,
                'color' => '#8b5cf6', // Violet
            ],
            [
                'name' => 'In Progress',
                'order' => 4,
                'color' => '#f59e0b', // Amber
            ],
            [
                'name' => 'QA',
                'order' => 5,
                'color' => '#10b981', // Emerald
                'requires_verification' => true
            ],
            [
                'name' => 'QA Review',
                'order' => 6,
                'color' => '#059669', // Dark Emerald
                'requires_approval' => true
            ],
            [
                'name' => 'Client Review',
                'order' => 7,
                'color' => '#0ea5e9', // Sky
                'is_client_visible' => true,
                'requires_approval' => true
            ],
            [
                'name' => 'Closed',
                'order' => 8,
                'color' => '#065f46', // Emerald 800
                'is_final' => true,
                'is_client_visible' => true
            ]
        ];

        foreach ($stages as $config) {
            WorkflowStage::create([
                'workflow_id' => $workflow->id,
                'name' => $config['name'],
                'stage_order' => $config['order'],
                'color' => $config['color'],
                'is_final' => $config['is_final'] ?? false,
                'is_client_visible' => $config['is_client_visible'] ?? false,
                'requires_verification' => $config['requires_verification'] ?? false,
                'requires_approval' => $config['requires_approval'] ?? false,
                'approver_type' => ($config['requires_approval'] ?? false) ? 'role' : 'specific_user'
            ]);
        }
    }
}
