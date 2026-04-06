<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;
use App\Models\AttendancePolicy;
use App\Models\Holiday;
use App\Models\Tenant;

class SeedAttendanceConfig extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = Tenant::first()->id ?? 1;

        // 1. Default General Shift
        $generalShift = Shift::firstOrCreate(
            ['name' => 'General Shift'],
            [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'work_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'grace_late_entry' => 15,
                'grace_early_exit' => 10,
                'break_policy' => ['duration' => 60, 'paid' => true],
                'is_default' => true,
                'tenant_id' => $tenantId
            ]
        );

        // 2. Default Policy
        AttendancePolicy::firstOrCreate(
            ['name' => 'Standard Policy'],
            [
                'late_mark_threshold' => 3,
                'deduction_rule' => ['deduct_leave' => 0.5, 'type' => 'Casual Leave'],
                'overtime_rule' => ['rate' => 1.0, 'min_minutes' => 30],
                'sandwich_rule_enabled' => false,
                'tenant_id' => $tenantId
            ]
        );

        // 3. Holidays (Sample 2024/2025)
        $holidays = [
            ['name' => 'New Year', 'date' => '2025-01-01', 'type' => 'Fixed'],
            ['name' => 'Republic Day', 'date' => '2025-01-26', 'type' => 'Fixed'],
            ['name' => 'Holi', 'date' => '2025-03-14', 'type' => 'Restricted'],
            ['name' => 'Independence Day', 'date' => '2025-08-15', 'type' => 'Fixed'],
            ['name' => 'Diwali', 'date' => '2025-10-20', 'type' => 'Restricted'],
            ['name' => 'Christmas', 'date' => '2025-12-25', 'type' => 'Fixed'],
        ];

        foreach ($holidays as $h) {
            Holiday::firstOrCreate(
                ['name' => $h['name'], 'date' => $h['date']],
                [
                    'type' => $h['type'],
                    'applies_to_locations' => ['All'],
                    'is_recurring' => true,
                    'tenant_id' => $tenantId
                ]
            );
        }
    }
}
