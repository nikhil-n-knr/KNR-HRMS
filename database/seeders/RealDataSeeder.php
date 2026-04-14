<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\ProjectStage;
use App\Models\Task;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Identify and Preserve System Admin
            $adminEmail = 'admin@test.com';
            $admin = User::where('email', $adminEmail)->first();
            $adminId = $admin ? $admin->id : 1;

            if (!$admin) {
                $this->command->warn("System Admin not found. Creating a new one.");
                $tenant = Tenant::firstOrCreate(['slug' => 'knr'], ['name' => 'KNR International']);
                $admin = User::create([
                    'name' => 'System Admin',
                    'email' => $adminEmail,
                    'password' => Hash::make('Password@123'),
                    'tenant_id' => $tenant->id,
                    'status' => 'active',
                ]);
                $adminId = $admin->id;
            }

            // 2. Clean up existing data (Safe deletion with relationships)
            $this->command->info("Cleaning up existing project and employee data...");
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            Task::query()->delete();
            ProjectModule::query()->delete();
            ProjectStage::query()->delete();
            Project::query()->delete();
            
            // Delete all users except admin
            User::where('id', '!=', $adminId)->delete();
            Employee::query()->delete();
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 3. Create Default Client
            $client = Client::firstOrCreate(
                ['name' => 'KNR International', 'code' => 'KNRINT'],
                [
                    'contact_person' => 'Dr. Sathish Rajasekhar',
                    'email' => 'sathish.rajasekhar@knrint.com',
                    'tenant_id' => $admin->tenant_id
                ]
            );

            // 4. Create Employees
            $employeesData = [
                ['code' => 'KNR10482', 'name' => 'Dr. Sathish Rajasekhar', 'designation' => 'Founder & Chairman', 'email' => 'sathish.rajasekhar@knrint.com'],
                ['code' => 'KNR59304', 'name' => 'Nikhil N', 'designation' => 'IT Solution Architect', 'email' => 'nikhil.n@knrint.com'],
                ['code' => 'KNR84726', 'name' => 'A Rajshekhar', 'designation' => 'User Experience and Design', 'email' => 'rajshekhar.a@knrint.com'],
                ['code' => 'KNR31578', 'name' => 'Bhagyashree', 'designation' => 'Quality Assurance', 'email' => 'bhagyashree.basavaraj@knrint.com'],
                ['code' => 'KNR54092', 'name' => 'Bhoomika', 'designation' => 'Embedded/Firmware', 'email' => 'bhoomika.bs@knrint.com'],
                ['code' => 'KNR92641', 'name' => 'Chandana P', 'designation' => 'Product Delivery', 'email' => 'chandana.prabhudev@knrint.com'],
                ['code' => 'KNR77420', 'name' => 'Mahadev', 'designation' => 'AI and Automation', 'email' => 'mahadev.diwakar@knrint.com'],
                ['code' => 'KNR66815', 'name' => 'Harshitha', 'designation' => 'Business Data and Intelligence', 'email' => 'harshitha.h.p@knrint.com'],
                ['code' => 'KNR38967', 'name' => 'Naveen', 'designation' => 'Product Support', 'email' => 'v.naveen@knrint.com'],
                ['code' => 'KNR22156', 'name' => 'Hemanth C', 'designation' => 'CFO', 'email' => 'hemanth@knrint.com'],
                ['code' => 'KNR77539', 'name' => 'Jessy', 'designation' => 'Client Manager', 'email' => 'jessy.nps@knrint.com'],
                ['code' => 'KNR66218', 'name' => 'Preetam A.P', 'designation' => 'CTO', 'email' => 'preetam@knrint.com'],
                ['code' => 'KNR99841', 'name' => 'Sarath B V', 'designation' => 'SDET', 'email' => 'sarath.b.v@knrint.com'],
            ];

            $this->command->info("Creating 13 employees...");
            foreach ($employeesData as $data) {
                // Split name
                $parts = explode(' ', $data['name']);
                $lastName = count($parts) > 1 ? array_pop($parts) : '';
                $firstName = implode(' ', $parts);

                $user = User::create([
                    'tenant_id' => $admin->tenant_id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('Password@123'),
                    'status' => 'active',
                ]);

                Employee::create([
                    'uuid' => (string) Str::uuid(),
                    'tenant_id' => $admin->tenant_id,
                    'user_id' => $user->id,
                    'employee_code' => $data['code'],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $data['email'],
                    'designation' => $data['designation'],
                    'status' => 'active',
                    'joining_date' => now(),
                ]);
            }

            // 5. Create Projects
            $projectsList = [
                'LEAP - Admin', 'LEAP - Mobile', 'LEAP - Web', 'LEAP - Teacher App',
                'ISaakshi - IGrantha', 'HRMS - KNR', 'CRM - KIM', 'CRM - KNR',
                'CMS - NPS', 'CMS - KNR', 'LMS - KNR', 'LMS - Grant(EV)'
            ];

            $this->command->info("Creating 12 projects and their tasks...");
            foreach ($projectsList as $projectName) {
                $project = Project::create([
                    'tenant_id' => $admin->tenant_id,
                    'client_id' => $client->id,
                    'name' => $projectName,
                    'code' => Str::upper(Str::slug($projectName, '')),
                    'status' => 'active',
                    'start_date' => now(),
                    'visibility' => 'public',
                ]);

                // Add Basic Tasks (as requested)
                $this->addBasicTasks($project, $adminId);

                // Add Hierarchy Tasks
                $this->addHierarchyTasks($project, $adminId);
            }
        });
    }

    private function addBasicTasks(Project $project, $adminId)
    {
        $categories = [
            'Planning & Discovery' => [
                'Requirement Gathering', 'Stakeholder Meetings', 'Scope Definition',
                'Feasibility Analysis', 'Timeline & Milestones'
            ],
            'System Design' => [
                'Architecture Design', 'Database Design', 'API Design',
                'UI/UX Wireframes', 'Module Mapping'
            ],
            'Development' => [
                'Backend Development', 'Frontend Development', 'API Integration',
                'Third-party Integrations', 'Role & Permission System'
            ],
            'Testing' => [
                'Unit Testing', 'Integration Testing', 'UAT (User Acceptance Testing)',
                'Performance Testing', 'Security Testing'
            ],
            'Deployment' => [
                'CI/CD Setup', 'Server Configuration', 'Domain & SSL Setup',
                'App Store / Play Store Deployment'
            ],
            'Maintenance & Support' => [
                'Bug Fixes', 'Monitoring', 'Feature Enhancements',
                'User Support', 'Scaling & Optimization'
            ]
        ];

        foreach ($categories as $catName => $tasks) {
            $module = ProjectModule::create([
                'project_id' => $project->id,
                'name' => $catName,
            ]);

            foreach ($tasks as $taskName) {
                Task::create([
                    'project_id' => $project->id,
                    'module_id' => $module->id,
                    'title' => $taskName,
                    'status' => 'pending',
                    'priority' => 'medium',
                    'created_by' => $adminId,
                ]);
            }
        }
    }

    private function addHierarchyTasks(Project $project, $adminId)
    {
        $mapping = [
            'LEAP - Admin' => 'LEAP_hierarchy.txt',
            'LEAP - Web' => 'LEAP_hierarchy.txt',
            'LEAP - Mobile' => 'Mobile_APP_hiracy.txt',
            'LEAP - Teacher App' => 'Teacher_App_hiracy.txt',
            'HRMS - KNR' => 'HRMS_hierarchy.txt',
            'CRM - KIM' => 'CRM_hierarchy.txt',
            'CRM - KNR' => 'CRM_hierarchy.txt',
            'CMS - NPS' => 'CMS_hieracy.txt',
            'CMS - KNR' => 'CMS_hieracy.txt',
        ];

        $projectName = $project->name;
        if (!isset($mapping[$projectName])) return;

        $filePath = base_path('tmp/' . $mapping[$projectName]);
        if (!File::exists($filePath)) return;

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $stack = []; // Stores ProjectModule objects at different levels

        foreach ($lines as $line) {
            if (trim($line) === '') continue;

            $indentLevel = floor((strlen($line) - strlen(ltrim($line))) / 4);
            $name = trim($line);

            // Determine Parent ID
            $parentId = null;
            if ($indentLevel > 0 && isset($stack[$indentLevel - 1])) {
                $parentId = $stack[$indentLevel - 1]->id;
            }

            // Create Module
            $module = ProjectModule::create([
                'project_id' => $project->id,
                'parent_id' => $parentId,
                'name' => $name,
            ]);

            // Update stack
            $stack[$indentLevel] = $module;

            // If it's a deep level, maybe also add a dummy task?
            // To be accurate, we will just create the module structure.
            // If it has no children in the next lines, it might be a task, but ProjectModule is safe.
        }
    }
}
