<?php

namespace App\Services\LMS;

use App\Models\LmsCourse;
use App\Models\LmsAssignment;
use App\Models\LmsAttempt;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get compliance matrix (Department x Course)
     */
    public function getComplianceMatrix(): array
    {
        $courses = LmsCourse::where('is_active', true)->get();
        $departments = Department::all();
        
        $matrix = [];
        
        foreach ($departments as $dept) {
            $row = [
                'department' => $dept->name,
                'courses' => []
            ];
            
            foreach ($courses as $course) {
                $stats = $this->getDepartmentCourseStats($dept->id, $course->id);
                $row['courses'][$course->id] = $stats;
            }
            
            $matrix[] = $row;
        }
        
        return [
            'matrix' => $matrix,
            'courses' => $courses->map(fn($c) => ['id' => $c->id, 'title' => $c->title])
        ];
    }
    
    /**
     * Get stats for department-course combination
     */
    private function getDepartmentCourseStats(int $deptId, int $courseId): array
    {
        $assignments = LmsAssignment::whereHas('employee', function($q) use ($deptId) {
            $q->where('department_id', $deptId);
        })->where('course_id', $courseId);
        
        $total = $assignments->count();
        $completed = (clone $assignments)->where('status', 'completed')->count();
        $overdue = (clone $assignments)->where('status', 'overdue')->count();
        
        $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;
        
        return [
            'total' => $total,
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => round($completionRate, 2),
            'status' => $this->getComplianceStatus($completionRate)
        ];
    }
    
    private function getComplianceStatus($rate): string
    {
        if ($rate >= 90) return 'excellent';
        if ($rate >= 70) return 'good';
        if ($rate >= 50) return 'warning';
        return 'critical';
    }
    
    /**
     * Get defaulter list (employees with overdue assignments)
     */
    public function getDefaulters(): array
    {
        $defaulters = LmsAssignment::with(['employee', 'course'])
            ->where('status', 'overdue')
            ->get()
            ->map(function($assignment) {
                return [
                    'employee_name' => $assignment->employee->full_name,
                    'emp_code' => $assignment->employee->emp_code,
                    'department' => $assignment->employee->department->name ?? 'N/A',
                    'course_title' => $assignment->course->title,
                    'due_date' => $assignment->due_date->format('M d, Y'),
                    'days_overdue' => now()->diffInDays($assignment->due_date)
                ];
            });
            
        return $defaulters->toArray();
    }
    
    /**
     * Get course performance analytics
     */
    public function getCourseAnalytics(int $courseId): array
    {
        $course = LmsCourse::findOrFail($courseId);
        
        $assignments = LmsAssignment::where('course_id', $courseId);
        $attempts = LmsAttempt::where('course_id', $courseId)->where('status', 'submitted');
        
        $totalAssigned = $assignments->count();
        $completed = (clone $assignments)->where('status', 'completed')->count();
        $inProgress = (clone $assignments)->where('status', 'in_progress')->count();
        $overdue = (clone $assignments)->where('status', 'overdue')->count();
        
        $totalAttempts = $attempts->count();
        $passed = (clone $attempts)->where('is_passed', true)->count();
        $failed = (clone $attempts)->where('is_passed', false)->count();
        
        $avgScore = (clone $attempts)->avg('percentage');
        $avgTime = (clone $attempts)->avg('time_spent_seconds');
        
        // Pass rate by attempt number
        $passRateByAttempt = [];
        for ($i = 1; $i <= 3; $i++) {
            $attemptCount = (clone $attempts)->where('attempt_number', $i)->count();
            $attemptPassed = (clone $attempts)
                ->where('attempt_number', $i)
                ->where('is_passed', true)
                ->count();
                
            $passRateByAttempt[$i] = $attemptCount > 0 
                ? round(($attemptPassed / $attemptCount) * 100, 2)
                : 0;
        }
        
        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'passing_score' => $course->passing_score
            ],
            'assignments' => [
                'total' => $totalAssigned,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'overdue' => $overdue,
                'completion_rate' => $totalAssigned > 0 ? round(($completed / $totalAssigned) * 100, 2) : 0
            ],
            'attempts' => [
                'total' => $totalAttempts,
                'passed' => $passed,
                'failed' => $failed,
                'pass_rate' => $totalAttempts > 0 ? round(($passed / $totalAttempts) * 100, 2) : 0,
                'pass_rate_by_attempt' => $passRateByAttempt
            ],
            'performance' => [
                'avg_score' => round($avgScore ?? 0, 2),
                'avg_time_minutes' => round(($avgTime ?? 0) / 60, 2)
            ]
        ];
    }
    
    /**
     * Get employee training history
     */
    public function getEmployeeHistory(int $employeeId): array
    {
        $assignments = LmsAssignment::with(['course', 'attempts'])
            ->where('employee_id', $employeeId)
            ->get()
            ->map(function($assignment) {
                $latestAttempt = $assignment->attempts()
                    ->where('status', 'submitted')
                    ->latest()
                    ->first();
                    
                return [
                    'course_title' => $assignment->course->title,
                    'status' => $assignment->status,
                    'assigned_on' => $assignment->assigned_on->format('M d, Y'),
                    'due_date' => $assignment->due_date->format('M d, Y'),
                    'completed_at' => $assignment->completed_at?->format('M d, Y'),
                    'attempts_count' => $assignment->attempts->count(),
                    'latest_score' => $latestAttempt?->percentage,
                    'is_passed' => $latestAttempt?->is_passed ?? false
                ];
            });
            
        return $assignments->toArray();
    }
}
