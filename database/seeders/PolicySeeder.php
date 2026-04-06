<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendancePolicy;

class PolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AttendancePolicy::firstOrCreate(
            ['name' => 'Standard Policy'],
            [
                'tenant_id' => 1,
                'priority' => 10,
                'sandwich_rule_enabled' => false,
                'rules' => [
                    'grace_late_entry' => 15,
                    'half_day_hours' => 4.0
                ],
                'wfh_policy' => [
                    'min_minutes' => 480,
                    'latest_check_in' => '10:00'
                ],
                'overtime_policy' => [
                    'min_minutes' => 30,
                    'multiplier' => 1.5
                ],
                'timesheet_policy' => [
                    'daily_min_hours' => 8.0,
                    'daily_max_hours' => 12.0,
                    'allow_future_days' => false,
                    'require_project' => true
                ]
            ]
        );
    }
}
