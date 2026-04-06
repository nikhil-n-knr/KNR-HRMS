<?php

namespace App\Http\Controllers\HR\LMS;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Models\LmsAssignment;
use App\Models\LmsAttempt;
use App\Services\LMS\AssessmentService;
use App\Services\LMS\CertificateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    public function __construct(
        private AssessmentService $assessmentService,
        private CertificateService $certificateService
    ) {}
    
    /**
     * Show employee's assigned courses
     */
    public function myCourses(Request $request)
    {
        $employee = $request->user()->employee;
        
        if (!$employee) {
            return Inertia::render('LMS/MyCourses', [
                'assignments' => [],
                'message' => 'No employee profile found'
            ]);
        }
        
        $assignments = LmsAssignment::with(['course', 'attempts'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->get()
            ->map(function($assignment) use ($employee) {
                $latestAttempt = $assignment->attempts()
                    ->where('status', 'submitted')
                    ->latest()
                    ->first();
                    
                return [
                    'id' => $assignment->id,
                    'course' => $assignment->course,
                    'status' => $assignment->status,
                    'due_date' => $assignment->due_date,
                    'attempts_count' => $assignment->attempts->count(),
                    'max_attempts' => $assignment->course->max_attempts,
                    'latest_score' => $latestAttempt?->percentage,
                    'is_passed' => $latestAttempt?->is_passed ?? false,
                    'can_attempt' => $assignment->course->canEmployeeAttempt($employee)
                ];
            });
            
        return Inertia::render('LMS/MyCourses', [
            'assignments' => $assignments
        ]);
    }
    
    /**
     * Show course player interface
     */
    public function play(Request $request, LmsCourse $course)
    {
        $employee = $request->user()->employee;
        
        // Check if employee can attempt
        $canAttempt = $course->canEmployeeAttempt($employee);
        
        if (!$canAttempt['can_attempt']) {
            return back()->with('error', $canAttempt['reason']);
        }
        
        // Get assignment
        $assignment = LmsAssignment::where([
            'course_id' => $course->id,
            'employee_id' => $employee->id
        ])->latest()->firstOrFail();
        
        // Load course content
        $course->load('contents');
        
        // Check if there's an in-progress attempt
        $inProgressAttempt = $assignment->attempts()
            ->where('status', 'in_progress')
            ->first();
            
        return Inertia::render('LMS/Player', [
            'course' => $course,
            'assignment' => $assignment,
            'in_progress_attempt' => $inProgressAttempt,
            'attempts_used' => $assignment->attempts()->count(),
            'can_attempt' => $canAttempt
        ]);
    }
    
    /**
     * Start new attempt
     */
    public function start(Request $request, LmsCourse $course)
    {
        $employee = $request->user()->employee;
        
        // Verify can attempt
        $canAttempt = $course->canEmployeeAttempt($employee);
        if (!$canAttempt['can_attempt']) {
            return response()->json([
                'error' => $canAttempt['reason']
            ], 403);
        }
        
        $assignment = LmsAssignment::where([
            'course_id' => $course->id,
            'employee_id' => $employee->id
        ])->latest()->firstOrFail();
        
        // Check for existing in-progress attempts
        $existingAttempt = LmsAttempt::where([
            'assignment_id' => $assignment->id,
            'status' => 'in_progress'
        ])->first();
        
        if ($existingAttempt) {
            // Check if it's locked to a different session
            if ($existingAttempt->session_id && $existingAttempt->session_id !== session()->getId()) {
                return response()->json([
                    'error' => 'Exam is already in progress in another session'
                ], 409);
            }
            
            // Return existing attempt
            return response()->json([
                'attempt_id' => $existingAttempt->id,
                'questions' => $this->assessmentService->generateQuestionSet($course),
                'resumed' => true
            ]);
        }
        
        // Start new attempt
        $attempt = $this->assessmentService->startAttempt($assignment);
        
        // Lock attempt to current session
        $attempt->update([
            'session_id' => session()->getId(),
            'locked_at' => now()
        ]);
        
        // Get question set (use random test if enabled)
        if ($course->question_pool_size && $course->question_pool_size < $course->questions()->count()) {
            $randomQuestions = $this->assessmentService->generateRandomTest($course);
            $questions = $randomQuestions->map(function($q) use ($course) {
                return [
                    'id' => $q->id,
                    'type' => $q->type,
                    'question_text' => $q->question_text,
                    'scenario_context' => $q->scenario_context,
                    'image_url' => $q->image_path ? asset('storage/' . $q->image_path) : null,
                    'options' => collect($q->options)->map(fn($opt) => [
                        'id' => $opt['id'] ?? uniqid(),
                        'text' => $opt['text']
                    ])->toArray(),
                    'score_weight' => $q->score_weight
                ];
            })->toArray();
        } else {
            $questions = $this->assessmentService->generateQuestionSet($course);
        }
        
        return response()->json([
            'attempt_id' => $attempt->id,
            'questions' => $questions,
            'timer_minutes' => $course->timer_minutes,
            'config' => [
                'prevent_copy_paste' => $course->prevent_copy_paste,
                'track_tab_switches' => $course->track_tab_switches,
                'max_tab_switches' => $course->max_tab_switches
            ]
        ]);
    }
    
    /**
     * Submit attempt
     */
    public function submit(Request $request, LmsAttempt $attempt)
    {
        // Verify ownership
        if ($attempt->employee_id !== $request->user()->employee->id) {
            abort(403);
        }
        
        if ($attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Attempt already submitted'], 400);
        }
        
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required'
        ]);
        
        DB::transaction(function() use ($attempt, $validated) {
            // Update answers log
            $attempt->update([
                'answers_log' => $validated['answers']
            ]);
            
            // Grade attempt
            $this->assessmentService->gradeAttempt($attempt);
            
            // Generate certificate if passed
            if ($attempt->is_passed && $attempt->course->auto_generate_certificate) {
                $this->certificateService->generate($attempt);
            }
        });
        
        // Reload attempt with results
        $attempt->load(['certificate']);
        
        return response()->json([
            'success' => true,
            'result' => [
                'is_passed' => $attempt->is_passed,
                'score_obtained' => $attempt->score_obtained,
                'max_score' => $attempt->max_score,
                'percentage' => $attempt->percentage,
                'time_spent_seconds' => $attempt->time_spent_seconds,
                'certificate' => $attempt->certificate,
                'attempts_remaining' => $attempt->course->max_attempts - $attempt->attempt_number
            ]
        ]);
    }
    
    /**
     * Track anti-cheat violations
     */
    public function trackViolation(Request $request, LmsAttempt $attempt)
    {
        // Verify ownership
        if ($attempt->employee_id !== $request->user()->employee->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'type' => 'required|in:tab_switch,copy_attempt,paste_attempt'
        ]);
        
        if ($validated['type'] === 'tab_switch') {
            $attempt->recordTabSwitch();
            
            // Check if exceeded max switches
            if ($attempt->tab_switches_count >= $attempt->course->max_tab_switches) {
                // Auto-submit with current answers
                $this->assessmentService->gradeAttempt($attempt);
                
                return response()->json([
                    'action' => 'force_submit',
                    'message' => 'Maximum tab switches exceeded. Exam submitted automatically.'
                ]);
            }
            
            return response()->json([
                'action' => 'warning',
                'message' => "Warning {$attempt->tab_switches_count}/{$attempt->course->max_tab_switches}: Please do not leave the exam window.",
                'switches_remaining' => $attempt->course->max_tab_switches - $attempt->tab_switches_count
            ]);
        }
        
        if (in_array($validated['type'], ['copy_attempt', 'paste_attempt'])) {
            $attempt->recordCopyAttempt();
            
            return response()->json([
                'action' => 'warning',
                'message' => 'Copy/paste is disabled during the exam.'
            ]);
        }
        
        return response()->json(['success' => true]);
    }
}
