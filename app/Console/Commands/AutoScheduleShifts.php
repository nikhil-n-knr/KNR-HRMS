<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShiftRotation;
use App\Models\Shift;
use App\Models\User; // Employee
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoScheduleShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically schedule shifts based on assigned rotations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Auto-Scheduler...");

        $assignments = DB::table('employee_rotation')
            ->join('shift_rotations', 'employee_rotation.rotation_id', '=', 'shift_rotations.id')
            ->select('employee_rotation.*', 'shift_rotations.pattern', 'shift_rotations.frequency')
            ->get();

        foreach ($assignments as $assignment) {
            $pattern = json_decode($assignment->pattern);
            $currentStep = $assignment->current_step;
            $startDate = Carbon::parse($assignment->start_date);
            
            // Determine the shift for the NEXT period (e.g., tomorrow or next week)
            // For simplicity, let's schedule for "Tomorrow" if not exists
            $targetDate = Carbon::tomorrow();
            
            // Check if roster entry exists
            $exists = DB::table('shift_roster')
                ->where('employee_id', $assignment->employee_id)
                ->where('date', $targetDate->toDateString())
                ->exists();

            if (!$exists) {
                // Calculate which shift from pattern to apply
                // Logic depends on frequency. 
                // Weekly: Change pattern index every week.
                // Daily: Change pattern index every day.
                
                // Let's assume pattern is simply a list of Shifts to cycle through DAILY for now
                // Or if frequency is weekly, it means the entire week uses Pattern[0], next week Pattern[1]
                
                $shiftName = $pattern[$currentStep % count($pattern)];
                
                // Find Shift ID
                $shift = Shift::where('name', $shiftName)->first();
                
                if ($shift) {
                    DB::table('shift_roster')->insert([
                        'employee_id' => $assignment->employee_id,
                        'shift_id' => $shift->id,
                        'date' => $targetDate->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->info("Scheduled {$shiftName} for User {$assignment->employee_id} on {$targetDate->toDateString()}");
                } else {
                    $this->error("Shift {$shiftName} not found!");
                }
            }

            // Logic to advance step (e.g. if today is end of week)
            if ($assignment->frequency === 'weekly') {
                 if (Carbon::now()->isSunday()) {
                     DB::table('employee_rotation')
                        ->where('id', $assignment->id)
                        ->increment('current_step');
                 }
            } else {
                 // Daily rotation default
                 DB::table('employee_rotation')
                        ->where('id', $assignment->id)
                        ->increment('current_step');
            }
        }

        $this->info("Auto-Scheduling Complete.");
    }
}
