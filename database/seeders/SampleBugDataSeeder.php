<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BugTicket;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\User;
use App\Models\WorkflowStage;

class SampleBugDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $projects = Project::whereIn('code', ['CMP2', 'IHU-26', 'MAR-2026'])->get();
        $stages = WorkflowStage::all();
        
        if ($stages->isEmpty()) return;

        foreach ($projects as $project) {
            $modules = $project->modules;
            
            for ($i = 0; $i < 5; $i++) {
                $severity = ['low', 'medium', 'high', 'critical'][rand(0, 3)];
                $priority = ['low', 'normal', 'high', 'urgent'][rand(0, 3)];
                $module = $modules->random();
                $stage = $stages->random();

                BugTicket::create([
                    'project_id' => $project->id,
                    'module_id' => $module->id,
                    'subject' => "Sample issue in {$module->name} - " . rand(100, 999),
                    'description' => "This is a sample bug reported for the {$project->name} project in the {$module->name} module.",
                    'severity' => $severity,
                    'priority' => $priority,
                    'reporter_id' => $user->id,
                    'reporter_type' => User::class,
                    'workflow_stage_id' => $stage->id,
                    'is_client_visible' => (rand(1, 4) > 1),
                ]);
            }
        }
    }
}
