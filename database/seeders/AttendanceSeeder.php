<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRegularization;
use App\Models\Employee;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::all();
        
        if($employees->count() === 0) {
            $this->command->info("No employees found. Run CompanySeeder first.");
            return;
        }

        foreach($employees->take(5) as $employee) {
            // Create a pending regularization
            AttendanceRegularization::create([
                'employee_id' => $employee->id,
                'date' => Carbon::yesterday()->format('Y-m-d'),
                'regularized_in_time' => '09:00:00',
                'regularized_out_time' => '18:00:00',
                'reason' => 'Forgot to punch out due to client meeting',
                'status' => 'Pending'
            ]);

             // Create an approved one
             AttendanceRegularization::create([
                'employee_id' => $employee->id,
                'date' => Carbon::today()->subDays(3)->format('Y-m-d'),
                'regularized_in_time' => '09:30:00',
                'regularized_out_time' => '18:30:00',
                'reason' => 'System Glitch',
                'status' => 'Approved',
                'approver_id' => 1, // Admin
                'approver_remarks' => 'Verified with IT'
            ]);
        }
        
        $this->command->info("Attendance Regularizations seeded.");
    }
}
