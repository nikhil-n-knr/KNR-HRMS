<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\Client;

class ProjectDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = Client::first();
        if (!$client) {
            $client = Client::create([
                'name' => 'Acme Corp',
                'email' => 'contact@acme.com',
                'industry' => 'Technology',
                'status' => 'active',
            ]);
        }

        $projects = [
            [
                'name' => 'Cloud Migration Phase 2',
                'code' => 'CMP2',
                'description' => 'Migrating secondary workloads to AWS with high-availability configurations.',
                'status' => 'active',
                'visibility' => 'public',
                'start_date' => now(),
                'deadline' => now()->addMonths(6),
                'billing_type' => 'fixed',
            ],
            [
                'name' => 'Internal HRMS Upgrades',
                'code' => 'IHU-26',
                'description' => 'Major version upgrade for the internal HR and Payroll systems.',
                'status' => 'planning',
                'visibility' => 'team_locked',
                'start_date' => now()->addDays(15),
                'deadline' => now()->addYear(),
                'billing_type' => 'hourly',
                'hourly_rate' => 75.00,
                'currency' => 'USD'
            ],
            [
                'name' => 'Mobile App Revamp',
                'code' => 'MAR-2026',
                'description' => 'Full redesign and Flutter migration for the customer-facing mobile application.',
                'status' => 'on_hold',
                'visibility' => 'public',
                'start_date' => now()->subMonths(2),
                'deadline' => now()->addMonths(4),
                'billing_type' => 'fixed'
            ]
        ];

        foreach ($projects as $projData) {
            $project = Project::updateOrCreate(
                ['code' => $projData['code']],
                array_merge($projData, ['client_id' => $client->id])
            );

            // Add Modules
            $modules = [
                ['name' => 'Infrastructure', 'description' => 'Networking and Server setup'],
                ['name' => 'Database', 'description' => 'Schema and Migration scripts'],
                ['name' => 'Security', 'description' => 'IAM and Encryption layer'],
                ['name' => 'UI/UX Design', 'description' => 'Wireframes and Asset creation'],
                ['name' => 'API Integration', 'description' => 'Backend bridge and payload mapping'],
            ];

            foreach ($modules as $modData) {
                ProjectModule::updateOrCreate(
                    ['project_id' => $project->id, 'name' => $modData['name']],
                    $modData
                );
            }
            
            // Add some sub-modules for the first module
            $parent = ProjectModule::where('project_id', $project->id)->where('name', 'Infrastructure')->first();
            if ($parent) {
                $subs = ['Network Virtualization', 'Container Orchestration', 'Log Aggregation'];
                foreach ($subs as $subName) {
                    ProjectModule::updateOrCreate(
                        ['project_id' => $project->id, 'name' => $subName, 'parent_id' => $parent->id],
                        ['description' => "Sub-module for {$parent->name}"]
                    );
                }
            }
        }
    }
}
