<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Shift;
use Carbon\Carbon;

class SyncAdminEmployeeSeeder extends Seeder
{
    /**
     * Ensure the Admin User has an Employee Record.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@test.com')->first();
        
        if (!$user) {
            $this->command->error("Admin user not found. Run DatabaseSeeder first.");
            return;
        }

        // 1. Ensure Department
        $dept = Department::firstOrCreate(
            ['name' => 'Administration'],
            ['tenant_id' => $user->tenant_id]
        );

        // 2. Ensure Shift (Default)
        // Note: Employee table does NOT have shift_id, logic is in Service.
        $shift = Shift::firstOrCreate(
            ['name' => 'General Shift'],
            [
                'tenant_id' => $user->tenant_id,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'grace_period_minutes' => 15,
                'is_active' => true,
                'is_default' => true // Mark as default so Registry picks it up
            ]
        );

        // 3. Create Employee Profile
        if (!$user->employee) {
            $employee = Employee::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'employee_code' => 'ADM001',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'department_id' => $dept->id,
                'designation' => 'System Administrator', // String column
                'joining_date' => Carbon::now(),
                'status' => 'active',
                'email' => $user->email,
            ]);
            $this->command->info("Created Employee Profile for Admin: ADM001");
        } else {
            $this->command->info("Admin Employee Profile already exists.");
        }
    }
}
