<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class FacultyHubController extends Controller
{
    /**
     * Display the Faculty Hub dashboard.
     */
    public function index()
    {
        return Inertia::render('LMS/Faculty/Hub', [
            'stats' => [
                'active_courses' => 5,
                'pending_grades' => 12,
                'total_students' => 145,
                'live_sessions_today' => 2
            ],
            'courses' => [
                [
                    'id' => 1,
                    'title' => 'Advanced EV Powertrains',
                    'enrollment' => 45,
                    'progress' => 65,
                    'pending_tasks' => 3
                ],
                [
                    'id' => 2,
                    'title' => 'Battery Management Systems',
                    'enrollment' => 100,
                    'progress' => 40,
                    'pending_tasks' => 9
                ]
            ],
            'pending_submissions' => [
                [
                    'id' => 101,
                    'student_name' => 'Nikhil Soni',
                    'course' => 'Advanced EV Powertrains',
                    'assignment' => 'Final Logic Design',
                    'submitted_at' => now()->subHours(2)->toDateTimeString()
                ],
                [
                    'id' => 102,
                    'student_name' => 'Amit Sharma',
                    'course' => 'Battery Management Systems',
                    'assignment' => 'Cell Equalization Lab',
                    'submitted_at' => now()->subHours(5)->toDateTimeString()
                ]
            ]
        ]);
    }

    /**
     * Display course-specific details for the faculty.
     */
    public function courseDetail($courseId)
    {
        return Inertia::render('LMS/Faculty/CourseDetail', [
            'courseId' => $courseId
        ]);
    }

    /**
     * Handle batch broadcasting/messages.
     */
    public function sendMessage(Request $request)
    {
        // Logic for sending in-app/email notifications to a batch
        return back()->with('success', 'Message broadcasted successfully.');
    }
}
