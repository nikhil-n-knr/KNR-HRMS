<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SeedSampleNotifications extends Seeder
{
    public function run()
    {
        // Find Super Admin (assuming role or just first user)
        $user = User::whereHas('roles', function($q){
            $q->where('name', 'Super Admin');
        })->first();

        // Fallback to first user if no super admin found easily
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->command->error('No users found to seed notifications!');
            return;
        }

        $this->command->info("Seeding notifications for user: {$user->name} ({$user->email})");

        $notifications = [
            [
                'id' => Str::uuid(),
                'type' => 'App\Notifications\ApprovalRequested',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'title' => 'Leave Request Approval',
                    'message' => 'John Doe has requested Annual Leave from 20th Oct to 22nd Oct.',
                    'action_url' => '/dashboard',
                    'type' => 'Approval' // Custom key for frontend icon logic
                ]),
                'read_at' => null,
                'created_at' => Carbon::now()->subMinutes(5),
                'updated_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'id' => Str::uuid(),
                'type' => 'App\Notifications\SystemAlert',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'title' => 'System Update Scheduled',
                    'message' => 'The HRMS system will undergo maintenance on Saturday at 10:00 PM.',
                    'action_url' => null,
                    'type' => 'Alert'
                ]),
                'read_at' => null,
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ],
            [
                'id' => Str::uuid(),
                'type' => 'App\Notifications\GeneralInfo',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'title' => 'Welcome to Nexus HR',
                    'message' => 'Get started by setting up your profile and exploring the dashboard.',
                    'action_url' => '/admin/employees',
                    'type' => 'Info'
                ]),
                'read_at' => Carbon::now(),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];

        DB::table('notifications')->insert($notifications);

        $this->command->info('Sample notifications seeded successfully!');
    }
}
