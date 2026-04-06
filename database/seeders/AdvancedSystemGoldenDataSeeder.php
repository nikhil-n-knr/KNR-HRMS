<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Holiday;
use App\Models\AttendancePolicy;
use App\Models\Shift;
use App\Models\Team;
use App\Models\Department;
use App\Models\Badge;
use App\Models\PointRule;
use App\Models\EmployeePoint;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdvancedSystemGoldenDataSeeder extends Seeder
{
    public function run()
    {
        $tenant = Tenant::first() ?? Tenant::create(['name' => 'Golden Corp', 'slug' => 'golden', 'domain' => 'golden.test']);
        $admin = User::where('email', 'admin@test.com')->first();
        if (!$admin) {
             $admin = User::first(); 
        }

        // 1. Leave Types
        $leaveTypes = [
            ['name' => 'Sick Leave', 'code' => 'SL', 'days' => 12, 'is_paid' => true, 'color' => '#EF4444'],
            ['name' => 'Casual Leave', 'code' => 'CL', 'days' => 12, 'is_paid' => true, 'color' => '#F59E0B'],
            ['name' => 'Annual Leave', 'code' => 'AL', 'days' => 18, 'is_paid' => true, 'color' => '#10B981'],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'days' => 180, 'is_paid' => true, 'color' => '#EC4899'],
            ['name' => 'Paternity Leave', 'code' => 'PL', 'days' => 15, 'is_paid' => true, 'color' => '#3B82F6'],
            ['name' => 'Comp-Off', 'code' => 'CO', 'days' => 0, 'is_paid' => true, 'color' => '#8B5CF6'],
            ['name' => 'Leave Without Pay', 'code' => 'LWP', 'days' => 365, 'is_paid' => false, 'color' => '#6B7280'],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::updateOrCreate(
                ['code' => $lt['code'], 'tenant_id' => $tenant->id],
                [
                    'name' => $lt['name'],
                    'days_allowed_per_year' => $lt['days'],
                    'is_paid' => $lt['is_paid'],
                    'color' => $lt['color'],
                    'requires_approval' => true,
                    'is_active' => true
                ]
            );
        }

        // 2. Holidays 2025 (Standard Indian Holidays)
        $holidays = [
            ['date' => '2025-01-26', 'name' => 'Republic Day', 'type' => 'Fixed'],
            ['date' => '2025-03-14', 'name' => 'Holi', 'type' => 'Fixed'],
            ['date' => '2025-08-15', 'name' => 'Independence Day', 'type' => 'Fixed'],
            ['date' => '2025-10-02', 'name' => 'Gandhi Jayanti', 'type' => 'Fixed'],
            ['date' => '2025-10-20', 'name' => 'Diwali', 'type' => 'Fixed'],
            ['date' => '2025-12-25', 'name' => 'Christmas', 'type' => 'Fixed'],
        ];

        foreach ($holidays as $h) {
            Holiday::updateOrCreate(
                ['date' => $h['date'], 'tenant_id' => $tenant->id],
                ['name' => $h['name'], 'type' => $h['type'], 'is_recurring' => true]
            );
        }

        // 3. Attendance Policies & Shifts
        $shift = Shift::updateOrCreate(
            ['code' => 'GEN', 'tenant_id' => $tenant->id],
            [
                'name' => 'General Shift',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'work_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'grace_late_entry' => 15,
                'grace_early_exit' => 5,
                'is_default' => true,
                'color' => '#3B82F6'
            ]
        );

        $policy = AttendancePolicy::updateOrCreate(
            ['name' => 'Global Attendance Rules', 'tenant_id' => $tenant->id],
            [
                'rules' => ['grace_late_entry' => 15, 'half_day_hours' => 4],
                'late_mark_threshold' => 3,
                'deduction_rule' => ['deduct_leave' => 0.5, 'type' => 'CL'],
                'overtime_policy' => ['min_minutes' => 60, 'rate' => 1.5],
                'sandwich_rule_enabled' => true,
                'priority' => 0
            ]
        );

        AttendancePolicy::updateOrCreate(
            ['name' => 'Standard Corporate Policy', 'tenant_id' => $tenant->id],
            [
                'rules' => ['grace_late_entry' => 10, 'half_day_hours' => 5],
                'late_mark_threshold' => 5,
                'deduction_rule' => ['deduct_leave' => 1, 'type' => 'LWP'],
                'overtime_policy' => ['min_minutes' => 30, 'rate' => 1.0],
                'sandwich_rule_enabled' => false,
                'priority' => 1
            ]
        );

        // 4. Teams & Alignment
        $engineering = Department::where('name', 'Engineering')->first();
        $hrDept = Department::where('name', 'HR')->first();

        $vikram = User::where('email', 'vikram@golden.test')->first();
        $anjali = User::where('email', 'anjali@golden.test')->first();

        $engTeam = Team::updateOrCreate(
            ['name' => 'Core Engineering'],
            ['manager_id' => $vikram?->id]
        );

        $hrTeam = Team::updateOrCreate(
            ['name' => 'People Ops'],
            ['manager_id' => $anjali?->id]
        );

        // Align employees to teams
        if ($vikram) $vikram->update(['team_id' => $engTeam->id, 'department_id' => $engineering?->id]);
        if ($anjali) $anjali->update(['team_id' => $hrTeam->id, 'department_id' => $hrDept?->id]);

        $rahul = User::where('email', 'rahul@golden.test')->first();
        if ($rahul) $rahul->update(['team_id' => $engTeam->id, 'department_id' => $engineering?->id]);

        $sneha = User::where('email', 'sneha@golden.test')->first();
        if ($sneha) $sneha->update(['team_id' => $engTeam->id, 'department_id' => $engineering?->id]);

        $amit = User::where('email', 'amit@golden.test')->first();
        if ($amit) $amit->update(['team_id' => $hrTeam->id, 'department_id' => $hrDept?->id]);

        // 5. Gamification (Points Economy & Badges)
        $rules = [
            [
                'category' => 'Attendance',
                'items' => [
                    ['key' => 'attendance.checkin.ontime', 'name' => 'On-Time Arrival', 'points' => 10, 'desc' => 'Arriving before shift start time.'],
                    ['key' => 'attendance.checkin.early', 'name' => 'Early Bird', 'points' => 15, 'desc' => 'Arriving 30+ minutes early.'],
                    ['key' => 'attendance.perfect_week', 'name' => 'Perfect Week', 'points' => 50, 'desc' => '5 consecutive work days without late/absence.'],
                ]
            ],
            [
                'category' => 'Performance',
                'items' => [
                    ['key' => 'performance.kpi.exceeded', 'name' => 'KPI Overachiever', 'points' => 100, 'desc' => 'Exceeding target KPIs by 10% or more.'],
                    ['key' => 'performance.appraisal.gold', 'name' => 'Appraisal Perfection', 'points' => 200, 'desc' => 'Receiving a 5.0 rating in annual appraisal.'],
                ]
            ],
            [
                'category' => 'Learning',
                'items' => [
                    ['key' => 'training.course.completed', 'name' => 'Knowledge Seeker', 'points' => 30, 'desc' => 'Completing an internal training course.'],
                    ['key' => 'training.certification.new', 'name' => 'Certified Pro', 'points' => 100, 'desc' => 'Attaining an external industry certification.'],
                ]
            ]
        ];

        foreach ($rules as $cat) {
            foreach ($cat['items'] as $item) {
                PointRule::updateOrCreate(
                    ['event_key' => $item['key']],
                    [
                        'event_category' => $cat['category'],
                        'name' => $item['name'],
                        'points' => $item['points'],
                        'description' => $item['desc'],
                        'is_active' => true
                    ]
                );
            }
        }

        $badges = [
            ['name' => 'Early Bird', 'slug' => 'early-bird', 'points' => 50, 'icon' => '🌅', 'desc' => 'Awarded for 10 on-time check-ins.'],
            ['name' => 'Attendance Master', 'slug' => 'attendance-master', 'points' => 200, 'icon' => '👑', 'desc' => 'Zero late marks in a full calendar month.'],
            ['name' => 'Social Star', 'slug' => 'social-star', 'points' => 25, 'icon' => '⭐', 'desc' => '100% profile completion.'],
            ['name' => 'Performance King', 'slug' => 'performance-king', 'points' => 500, 'icon' => '🥇', 'desc' => '3 consecutive 4.5+ appraisal ratings.'],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(
                ['slug' => $b['slug']],
                [
                    'name' => $b['name'],
                    'points_bonus' => $b['points'],
                    'icon' => $b['icon'],
                    'criteria_description' => $b['desc'],
                    'is_active' => true
                ]
            );
        }

        // 6. Workflows (Standard Leave Approval)
        $leaveWF = Workflow::updateOrCreate(
            ['entity_type' => 'leave_request', 'tenant_id' => $tenant->id],
            [
                'name' => 'Standard Leave Approval',
                'module' => 'LMS', // Aligning with common modules
                'description' => 'Manager -> HR approval flow',
                'trigger_event' => 'created',
                'is_active' => true,
                'priority' => 10,
                'approved_status' => 'Approved',
                'rejected_status' => 'Rejected'
            ]
        );

        // Stage 1: Department Manager
        WorkflowStage::updateOrCreate(
            ['workflow_id' => $leaveWF->id, 'stage_order' => 1],
            [
                'name' => 'Manager Approval',
                'approver_type' => 'manager',
                'can_reject' => true,
                'can_edit' => false,
                'approval_strategy' => 'any'
            ]
        );

        // Stage 2: HR Role
        $hrRole = Role::where('slug', 'hr_admin')->first();
        WorkflowStage::updateOrCreate(
            ['workflow_id' => $leaveWF->id, 'stage_order' => 2],
            [
                'name' => 'HR Verification',
                'approver_type' => 'role',
                'role_id' => $hrRole?->id,
                'can_reject' => true,
                'can_edit' => true,
                'approval_strategy' => 'any'
            ]
        );

        // ==========================================
        // 7. Bug Tracker & Client Portal Seeding (Phase 8/9 Data)
        // ==========================================
        $this->command->info('Seeding Bug Tracker & Client Portal Data...');

        // 7.1 Clients
        $acme = \App\Models\Client::updateOrCreate(
            ['code' => 'ACME'],
            [
                'name' => 'Acme Corp',
                'contact_person' => 'Wile E. Coyote',
                'email' => 'contact@acme.com',
                'portal_access' => true,
                'contract_start' => now()->subYear(),
                'contract_end' => now()->addYear()
            ]
        );

        $umbrella = \App\Models\Client::updateOrCreate(
            ['code' => 'UMB'],
            [
                'name' => 'Umbrella Corp',
                'contact_person' => 'Albert Wesker',
                'email' => 'contact@umbrella.com',
                'portal_access' => true,
                'contract_start' => now()->subMonth(),
                'contract_end' => now()->addYears(2)
            ]
        );

        // 7.2 Client Users
        // Admin for Acme
        $acmeUser = \App\Models\ClientUser::updateOrCreate(
            ['email' => 'admin@acme.com'],
            [
                'client_id' => $acme->id,
                'name' => 'Acme Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_active' => true
            ]
        );

        // Admin for Umbrella
        $umbrellaUser = \App\Models\ClientUser::updateOrCreate(
            ['email' => 'admin@umbrella.com'],
            [
                'client_id' => $umbrella->id,
                'name' => 'Umbrella Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_active' => true
            ]
        );

        // 7.3 Projects & Modules
        $p1 = \App\Models\Project::updateOrCreate(
            ['name' => 'Acme HRMS'],
            ['client_id' => $acme->id, 'status' => 'active', 'description' => 'Core HRMS implementation', 'code' => 'ACME-HRMS']
        );
        $acmeUser->projects()->syncWithoutDetaching([$p1->id]);

        $p2 = \App\Models\Project::updateOrCreate(
            ['name' => 'Umbrella CRM'],
            ['client_id' => $umbrella->id, 'status' => 'active', 'description' => 'Customer Relationship Management', 'code' => 'UMB-CRM']
        );
        $umbrellaUser->projects()->syncWithoutDetaching([$p2->id]);

        // Modules for P1
        $m1 = \App\Models\ProjectModule::updateOrCreate(['project_id' => $p1->id, 'name' => 'Authentication']);
        $m2 = \App\Models\ProjectModule::updateOrCreate(['project_id' => $p1->id, 'name' => 'Dashboard']);
        $m3 = \App\Models\ProjectModule::updateOrCreate(['project_id' => $p1->id, 'name' => 'Payroll']);

        // 7.4 Bug Tracking Workflow
        $bugWorkflow = Workflow::updateOrCreate(
            ['name' => 'Bug Tracking Pipeline', 'entity_type' => \App\Models\BugTicket::class],
            ['module' => 'Project', 'is_active' => true]
        );

        $stages = [
            ['name' => 'Open', 'order' => 1, 'is_client_visible' => true, 'is_final' => false, 'requires_verification' => false],
            ['name' => 'In Progress', 'order' => 2, 'is_client_visible' => true, 'is_final' => false, 'requires_verification' => false],
            ['name' => 'QA Review', 'order' => 3, 'is_client_visible' => false, 'is_final' => false, 'requires_verification' => false],
            ['name' => 'Client Review', 'order' => 4, 'is_client_visible' => true, 'is_final' => false, 'requires_verification' => true, 'auto_close_days' => 7],
            ['name' => 'Closed', 'order' => 5, 'is_client_visible' => true, 'is_final' => true, 'requires_verification' => false],
        ];

        foreach ($stages as $s) {
            WorkflowStage::updateOrCreate(
                ['workflow_id' => $bugWorkflow->id, 'name' => $s['name']],
                [
                    'stage_order' => $s['order'],
                    'is_client_visible' => $s['is_client_visible'],
                    'is_final' => $s['is_final'],
                    'requires_verification' => $s['requires_verification'],
                    'auto_close_days' => $s['auto_close_days'] ?? 0
                ]
            );
        }

        // 7.5 Seed Dummy Bugs
        $reporter = User::first(); // Internal reporter
        
        $bugData = [
            ['subject' => 'Login page crashes on Safari', 'severity' => 'critical', 'priority' => 'urgent', 'module_id' => $m1->id],
            ['subject' => 'Dashboard widgets not loading', 'severity' => 'high', 'priority' => 'high', 'module_id' => $m2->id],
            ['subject' => 'Payroll export timeout', 'severity' => 'medium', 'priority' => 'normal', 'module_id' => $m3->id],
            ['subject' => 'Typo in welcome email', 'severity' => 'low', 'priority' => 'low', 'module_id' => $m1->id],
        ];

        foreach ($bugData as $data) {
            \App\Models\BugTicket::create([
                'project_id' => $p1->id,
                'module_id' => $data['module_id'],
                'subject' => $data['subject'],
                'description' => 'Automated test description for ' . $data['subject'],
                'severity' => $data['severity'],
                'priority' => $data['priority'],
                'reporter_id' => $reporter->id,
                'reporter_type' => User::class,
                'workflow_stage_id' => WorkflowStage::where('workflow_id', $bugWorkflow->id)->where('name', 'Open')->first()->id,
                'is_client_visible' => true
            ]);
        }

        $this->command->info('Advanced System Golden Data Seeded Successfully!');
    }
}
