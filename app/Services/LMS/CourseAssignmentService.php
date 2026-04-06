<?php

namespace App\Services\LMS;

use App\Models\LmsCourse;
use App\Models\LmsAssignment;
use App\Notifications\LMS\CourseAssignedNotification;
use App\Notifications\LMS\TrainingReminderNotification;
use Illuminate\Support\Facades\Log;

class CourseAssignmentService
{
    
    /**
     * Auto-assign course to employees based on target audience
     */
    public function autoAssign(LmsCourse $course): array
    {
        Log::context(['course_id' => $course->id]);
        Log::info('Starting auto-assignment', ['title' => $course->title]);
        
        $employees = $course->getTargetEmployees();
        $assigned = 0;
        $skipped = 0;
        
        foreach ($employees as $employee) {
            // Check if already assigned
            $existing = LmsAssignment::where([
                'course_id' => $course->id,
                'employee_id' => $employee->id
            ])->whereIn('status', ['pending', 'in_progress'])->exists();
            
            if ($existing) {
                $skipped++;
                continue;
            }
            
            $dueDate = $this->calculateDueDate($course, $employee);
            $validUntil = $dueDate->copy()->addDays($course->validity_days);
            
            LmsAssignment::create([
                'course_id' => $course->id,
                'employee_id' => $employee->id,
                'assigned_on' => now(),
                'due_date' => $dueDate,
                'valid_until' => $validUntil,
                'assigned_by' => auth()->id(),
                'status' => 'pending'
            ]);
            
            // Send notification
            if ($employee->user) {
                $employee->user->notify(new CourseAssignedNotification(
                    $course->title,
                    $dueDate->format('M d, Y'),
                    route('lms.play', $course->id)
                ));
            }
            
            $assigned++;
        }
        
        Log::info('Auto-assignment completed', [
            'assigned' => $assigned,
            'skipped' => $skipped,
            'total_employees' => $employees->count()
        ]);
        
        return compact('assigned', 'skipped');
    }
    
    /**
     * Calculate due date based on course configuration
     */
    private function calculateDueDate(LmsCourse $course, $employee)
    {
        if ($course->deadline_days_from_joining) {
            // Due X days from joining
            return $employee->joining_date
                ->addDays($course->deadline_days_from_joining);
        }
        
        // Default: 30 days from assignment
        return now()->addDays(30);
    }
    
    /**
     * Check and mark overdue assignments
     */
    public function markOverdue(): int
    {
        $updated = LmsAssignment::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
            
        Log::info("Marked {$updated} assignments as overdue");
        
        return $updated;
    }
    
    /**
     * Send reminders to employees with pending assignments
     */
    public function sendReminders(int $daysBefore = 3): int
    {
        $reminderDate = now()->addDays($daysBefore)->toDateString();
        
        $assignments = LmsAssignment::with(['employee.user', 'course'])
            ->where('status', 'pending')
            ->whereDate('due_date', $reminderDate)
            ->get();
            
        $sent = 0;
        
        foreach ($assignments as $assignment) {
            if ($assignment->employee->user) {
                $assignment->employee->user->notify(new TrainingReminderNotification(
                    $assignment->course->title,
                    $daysBefore,
                    route('lms.play', $assignment->course->id)
                ));
                $sent++;
            }
        }
        
        Log::info("Sent {$sent} training reminders");
        
        return $sent;
    }
}
