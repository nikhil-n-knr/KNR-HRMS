<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsConcept;
use App\Models\LMS\LmsActivity;
use App\Models\LMS\LmsVideoLesson;
use App\Models\LMS\LmsVideoSession;
use App\Models\LMS\LmsQuizConfig;
use App\Models\LMS\LmsQuizAttempt;
use App\Models\LMS\LmsQuizResponse;
use App\Models\LMS\LmsQuestionV2;
use App\Models\LMS\LmsAssignmentV2;
use App\Models\LMS\LmsAssignmentSubmission;
use App\Models\LMS\LmsConceptProgress;
use App\Models\LMS\LmsCourseProgress;
use App\Models\LMS\LmsCertificateV2;
use App\Models\LmsCourse;
use App\Services\LMS\ProgressAggregationService;
use App\Services\LMS\CertificateEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LearnerController extends Controller
{
    public function __construct(
        private ProgressAggregationService $progressService,
        private CertificateEligibilityService $certService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // MY LEARNING HUB
    // ─────────────────────────────────────────────────────────────────────

    public function hub(Request $request)
    {
        $userId      = auth()->id();
        $enrollments = LmsEnrollment::with(['course.category', 'courseProgress'])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->get();

        $certificates = LmsCertificateV2::where('user_id', $userId)
            ->where('is_revoked', false)
            ->with(['course', 'program'])
            ->latest('issued_at')
            ->take(5)
            ->get();

        $upcomingSessions = \App\Models\LMS\LmsLiveSession::whereHas('course.enrollments', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->take(5)
            ->with('host', 'course')
            ->get();

        $stats = [
            'enrolled'        => $enrollments->count(),
            'completed'       => $enrollments->filter(fn($e) => optional($e->courseProgress)->is_completed)->count(),
            'certificates'    => LmsCertificateV2::where('user_id', $userId)->where('is_revoked', false)->count(),
            'total_watch_hrs' => (DB::table('lms_video_sessions')->where('user_id', $userId)->sum('total_watch_seconds') ?? 0) / 3600,
            'points'          => \App\Models\LMS\LmsUserPoints::where('user_id', $userId)->sum('points') ?? 0,
        ];

        return Inertia::render('LMS/Learn/Hub', [
            'enrollments'      => $enrollments,
            'certificates'     => $certificates,
            'upcoming_sessions'=> $upcomingSessions,
            'stats'            => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // COURSE DETAIL (Learner view with full curriculum tree + their progress)
    // ─────────────────────────────────────────────────────────────────────

    public function courseDetail(LmsCourse $course)
    {
        $userId = auth()->id();

        $enrollment = LmsEnrollment::where(['user_id' => $userId, 'course_id' => $course->id])->first();
        if (!$enrollment) {
            abort(403, 'You are not enrolled in this course.');
        }

        // Full curriculum tree with progress
        $course->load([
            'modules' => fn($q) => $q->ordered()->where('is_active', true)->with([
                'chapters' => fn($q) => $q->ordered()->where('is_active', true)->with([
                    'concepts' => fn($q) => $q->ordered()->where('is_active', true)->with([
                        'activities' => fn($q) => $q->ordered()->where('is_active', true)
                    ])
                ])
            ])
        ]);

        // Learner's concept progress
        $conceptProgressMap = LmsConceptProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollment->id,
        ])->pluck('status', 'concept_id')->toArray();

        $courseProgress = LmsCourseProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollment->id,
        ])->first();

        // Certificate eligibility meter
        $eligibilityMeter = null;
        if ($course->certificateRule) {
            $eligibilityMeter = $this->certService->getEligibilityMeter($userId, $enrollment->id, $course->certificateRule);
        }

        $certificates = LmsCertificateV2::where(['user_id' => $userId, 'course_id' => $course->id])
            ->where('is_revoked', false)
            ->get();

        return Inertia::render('LMS/Learn/CourseDetail', [
            'course'            => $course,
            'enrollment'        => $enrollment,
            'course_progress'   => $courseProgress,
            'concept_progress'  => $conceptProgressMap,
            'eligibility_meter' => $eligibilityMeter,
            'certificates'      => $certificates,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // DEEP PLAYER (Full immersive concept view)
    // ─────────────────────────────────────────────────────────────────────

    public function playConcept(LmsCourse $course, LmsConcept $concept)
    {
        $userId     = auth()->id();
        $enrollment = LmsEnrollment::where(['user_id' => $userId, 'course_id' => $course->id])->firstOrFail();

        // Load concept with all activities + their details
        $concept->load([
            'activities' => fn($q) => $q->ordered()->where('is_active', true)->with([
                'videoLesson', 'readingMaterial',
                'quizConfig.attempts' => fn($q) => $q->where('user_id', $userId)->orderByDesc('attempt_number'),
                'assignment.submissions' => fn($q) => $q->where('user_id', $userId)->orderByDesc('submission_number'),
                'liveSession.attendance' => fn($q) => $q->where('user_id', $userId),
            ]),
            'chapter.module',
        ]);

        // Video session (resume state)
        $videoSessions = [];
        foreach ($concept->activities->where('type', 'video') as $activity) {
            $vs = LmsVideoSession::firstOrCreate(
                ['video_lesson_id' => $activity->videoLesson?->id, 'user_id' => $userId],
                ['activity_id' => $activity->id, 'enrollment_id' => $enrollment->id]
            );
            $videoSessions[$activity->id] = $vs;
        }

        // Concept progress
        $conceptProgress = LmsConceptProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollment->id,
            'concept_id'    => $concept->id,
        ])->first();

        // Adjacent concepts for navigation
        $allConcepts = LmsConcept::where('chapter_id', $concept->chapter_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->select('id', 'title', 'sort_order')
            ->get();
        $currentIndex = $allConcepts->search(fn($c) => $c->id === $concept->id);
        $prevConcept  = $currentIndex > 0 ? $allConcepts[$currentIndex - 1] : null;
        $nextConcept  = $currentIndex < $allConcepts->count() - 1 ? $allConcepts[$currentIndex + 1] : null;

        return Inertia::render('LMS/Learn/Player', [
            'course'           => $course->only('id', 'title', 'slug'),
            'concept'          => $concept,
            'enrollment'       => $enrollment->only('id', 'status'),
            'video_sessions'   => $videoSessions,
            'concept_progress' => $conceptProgress,
            'prev_concept'     => $prevConcept,
            'next_concept'     => $nextConcept,
        ]);
    }

    public function markConceptComplete(Request $request, LmsConcept $concept)
    {
        $userId = auth()->id();
        $concept->load('chapter.module');
        $courseId = $concept->chapter->module->course_id;

        $enrollment = LmsEnrollment::where(['user_id' => $userId, 'course_id' => $courseId])->firstOrFail();

        LmsConceptProgress::updateOrCreate(
            ['user_id' => $userId, 'enrollment_id' => $enrollment->id, 'concept_id' => $concept->id],
            [
                'chapter_id' => $concept->chapter_id,
                'module_id'  => $concept->module_id,
                'course_id'  => $courseId,
                'status' => 'completed',
                'completed_at' => now(),
                'completion_pct' => 100,
            ]
        );

        $this->progressService->aggregateCourseProgress($enrollment);
        $this->certService->checkAndAward($userId, $courseId, $enrollment->id);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // VIDEO TRACKING API (Called from player frontend via heartbeat)
    // ─────────────────────────────────────────────────────────────────────

    public function videoHeartbeat(Request $request, LmsVideoLesson $videoLesson)
    {
        $request->validate([
            'current_position' => 'required|integer|min:0',
            'segment_start'    => 'required|integer|min:0',
            'segment_end'      => 'required|integer|min:0',
            'enrollment_id'    => 'required|integer',
        ]);

        $userId     = auth()->id();
        $enrollment = LmsEnrollment::findOrFail($request->enrollment_id);

        $session = LmsVideoSession::firstOrCreate(
            ['video_lesson_id' => $videoLesson->id, 'user_id' => $userId],
            ['activity_id' => $videoLesson->activity_id, 'enrollment_id' => $enrollment->id]
        );

        // Anti-fast-forward check
        if ($videoLesson->disable_fast_forward) {
            $maxAllowed = $session->max_position_seconds + $videoLesson->seek_buffer_seconds;
            if ($request->current_position > $maxAllowed + 5) {
                return response()->json(['error' => 'seeking_not_allowed', 'max_position' => $session->max_position_seconds], 422);
            }
        }

        $session->recordHeartbeat(
            $request->current_position,
            [$request->segment_start, $request->segment_end],
            $request->segment_end - $request->segment_start,
        );

        // If video just completed, trigger progress aggregation
        if ($session->is_completed) {
            $this->onVideoCompleted($userId, $enrollment, $videoLesson);
        }

        return response()->json([
            'ok'                   => true,
            'is_completed'         => $session->is_completed,
            'completion_pct'       => $session->completion_pct,
            'total_watch_seconds'  => $session->total_watch_seconds,
            'last_position'        => $session->last_position_seconds,
        ]);
    }

    public function videoCheckpointAnswer(Request $request, LmsVideoLesson $videoLesson)
    {
        $request->validate([
            'checkpoint_id' => 'required|integer',
            'answer'        => 'required',
            'enrollment_id' => 'required|integer',
        ]);

        $userId  = auth()->id();
        $session = LmsVideoSession::where(['video_lesson_id' => $videoLesson->id, 'user_id' => $userId])->firstOrFail();

        $checkpoints = $videoLesson->checkpoints ?? [];
        $checkpoint  = collect($checkpoints)->firstWhere('id', (int)$request->checkpoint_id);

        if (!$checkpoint || !is_array($checkpoint)) {
            return response()->json(['error' => 'checkpoint_not_found'], 404);
        }

        $correctAnswer = $checkpoint['correct_answer'] ?? null;
        $isCorrect     = $request->answer == $correctAnswer;

        if ($isCorrect) {
            $session->passCheckpoint((int)$request->checkpoint_id);
        }

        return response()->json([
            'is_correct'   => $isCorrect,
            'explanation'  => $checkpoint['explanation'] ?? null,
            'can_continue' => $isCorrect,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // QUIZ ENGINE
    // ─────────────────────────────────────────────────────────────────────

    public function startQuiz(Request $request, LmsActivity $activity)
    {
        $quizConfig = $activity->quizConfig ?? abort(404, 'Quiz not found');
        $userId     = auth()->id();

        $attemptCount = LmsQuizAttempt::where(['quiz_config_id' => $quizConfig->id, 'user_id' => $userId])
            ->whereNotIn('status', ['abandoned'])
            ->count();

        if ($attemptCount >= $quizConfig->max_attempts) {
            return response()->json(['error' => 'max_attempts_reached'], 422);
        }

        // Build question list
        $questions = $this->buildQuestionSet($quizConfig);

        $expiresAt = $quizConfig->duration_minutes
            ? now()->addMinutes($quizConfig->duration_minutes)
            : null;

        $attempt = LmsQuizAttempt::create([
            'quiz_config_id'   => $quizConfig->id,
            'activity_id'      => $activity->id,
            'user_id'          => $userId,
            'attempt_number'   => $attemptCount + 1,
            'status'           => 'in_progress',
            'question_sequence'=> $questions->pluck('id')->toArray(),
            'max_score'        => $questions->sum('marks'),
            'started_at'       => now(),
            'expires_at'       => $expiresAt,
        ]);

        // Sanitize questions for learner (remove correct answers)
        $sanitized = $questions->map(fn($q) => [
            'id'                  => $q->id,
            'type'                => $q->type,
            'question_text'       => $q->question_text,
            'question_image'      => $q->question_image,
            'options'             => $this->sanitizeOptions($q->options ?? []),
            'marks'               => $q->marks,
            'estimated_time_seconds' => $q->estimated_time_seconds,
        ]);

        return response()->json([
            'attempt_id'       => $attempt->id,
            'questions'        => $sanitized,
            'duration_minutes' => $quizConfig->duration_minutes,
            'expires_at'       => $expiresAt,
            'max_score'        => $attempt->max_score,
            'attempt_number'   => $attempt->attempt_number,
            'max_attempts'     => $quizConfig->max_attempts,
        ]);
    }

    public function submitQuiz(Request $request, LmsQuizAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        if ($attempt->status !== 'in_progress') {
            return response()->json(['error' => 'attempt_already_submitted'], 422);
        }

        $request->validate(['responses' => 'required|array']);

        $quizConfig = $attempt->quizConfig;
        $scoreObtained = 0;
        $maxScore      = 0;

        // Grade each response
        foreach ($request->responses as $resp) {
            $question = LmsQuestionV2::find($resp['question_id']);
            if (!$question) continue;

            $isCorrect = $question->checkAnswer($resp['answer'] ?? null);
            $score     = $isCorrect ? $question->marks : ($quizConfig->enable_negative_marking ? -$question->negative_marks : 0);
            $score     = max(0, $score); // floor at 0

            LmsQuizResponse::create([
                'attempt_id'        => $attempt->id,
                'question_id'       => $question->id,
                'selected_answer'   => is_array($resp['answer']) ? $resp['answer'] : [$resp['answer']],
                'is_correct'        => $isCorrect,
                'score'             => $score,
                'time_spent_seconds'=> $resp['time_spent'] ?? 0,
                'is_skipped'        => empty($resp['answer']),
                'is_flagged'        => $resp['flagged'] ?? false,
            ]);

            $scoreObtained += $score;
            $maxScore      += $question->marks;

            // Update question stats
            $question->increment('usage_count');
        }

        $percentage = $maxScore > 0 ? round(($scoreObtained / $maxScore) * 100, 2) : 0;
        $isPassed   = $percentage >= $quizConfig->pass_mark_pct;
        $timeSpent  = now()->diffInSeconds($attempt->started_at);

        $attempt->update([
            'status'           => 'submitted',
            'score_obtained'   => $scoreObtained,
            'max_score'        => $maxScore,
            'percentage'       => $percentage,
            'is_passed'        => $isPassed,
            'time_spent_seconds'=> $timeSpent,
            'submitted_at'     => now(),
        ]);

        // Aggregate progress
        $activity   = $attempt->activity;
        $enrollment = LmsEnrollment::where(['user_id' => auth()->id(), 'course_id' => $activity->course_id])->first();
        if ($enrollment) {
            $this->progressService->recalculate(auth()->id(), $enrollment->id, $activity->concept_id);
            $awarded = $this->certService->checkAndAward(auth()->id(), $activity->course_id, $enrollment->id);
        }

        // Build feedback (per mode)
        $feedback = null;
        if ($quizConfig->feedback_mode === 'immediate' || $quizConfig->feedback_mode === 'end_of_quiz') {
            $feedback = $this->buildQuizFeedback($attempt);
        }

        return response()->json([
            'score'        => $scoreObtained,
            'max_score'    => $maxScore,
            'percentage'   => $percentage,
            'is_passed'    => $isPassed,
            'pass_mark_pct'=> $quizConfig->pass_mark_pct,
            'time_spent'   => $timeSpent,
            'feedback'     => $feedback,
            'certificates_awarded' => $awarded ?? [],
        ]);
    }

    public function trackViolation(Request $request, LmsQuizAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);

        $request->validate(['type' => 'required|in:tab_switch,copy_paste,window_blur,fullscreen_exit']);

        $violations = $attempt->violations ?? [];
        $violations[] = ['type' => $request->type, 'timestamp' => now()->toIso8601String()];

        $tabSwitches = $attempt->tab_switches_count + ($request->type === 'tab_switch' ? 1 : 0);
        $attempt->update(['violations' => $violations, 'tab_switches_count' => $tabSwitches]);

        $quizConfig  = $attempt->quizConfig;
        $autoSubmit  = $quizConfig->track_tab_switches && $tabSwitches >= $quizConfig->max_tab_switches;

        if ($autoSubmit) {
            // Auto-submit the attempt
            $request->merge(['responses' => []]);
            $this->submitQuiz($request, $attempt);
        }

        return response()->json(['ok' => true, 'auto_submitted' => $autoSubmit, 'tab_switches' => $tabSwitches, 'max_allowed' => $quizConfig->max_tab_switches]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // ASSIGNMENT SUBMISSION
    // ─────────────────────────────────────────────────────────────────────

    public function submitAssignment(Request $request, LmsActivity $activity)
    {
        $assignment = $activity->assignment ?? abort(404);
        $userId = auth()->id();

        $submissionCount = LmsAssignmentSubmission::where(['assignment_id' => $assignment->id, 'user_id' => $userId])->count();
        if ($submissionCount > 0 && !$assignment->allow_resubmission) {
            return response()->json(['error' => 'resubmission_not_allowed'], 422);
        }
        if ($submissionCount >= $assignment->max_resubmissions && $assignment->allow_resubmission) {
            return response()->json(['error' => 'max_resubmissions_reached'], 422);
        }

        $request->validate([
            'text_content'   => 'nullable|string',
            'submission_url' => 'nullable|url',
            'files'          => 'nullable|array',
            'files.*'        => 'file|max:' . ($assignment->max_file_size_mb * 1024),
        ]);

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store("lms/assignments/{$assignment->id}/submissions", 'public');
            }
        }

        $submission = LmsAssignmentSubmission::create([
            'assignment_id'     => $assignment->id,
            'activity_id'       => $activity->id,
            'user_id'           => $userId,
            'submission_number' => $submissionCount + 1,
            'status'            => 'submitted',
            'text_content'      => $request->text_content,
            'file_paths'        => $filePaths,
            'submission_url'    => $request->submission_url,
            'submitted_at'      => now(),
        ]);

        // Update progress
        $enrollment = LmsEnrollment::where(['user_id' => $userId, 'course_id' => $activity->course_id])->first();
        if ($enrollment) {
            $cp = LmsConceptProgress::firstOrCreate(
                ['user_id' => $userId, 'enrollment_id' => $enrollment->id, 'concept_id' => $activity->concept_id],
                ['chapter_id' => $activity->chapter_id, 'module_id' => $activity->module_id, 'course_id' => $activity->course_id]
            );
            $cp->update(['assignment_submitted' => true, 'last_activity_at' => now()]);
            $this->progressService->recalculate($userId, $enrollment->id, $activity->concept_id);
        }

        return response()->json(['submission' => $submission, 'ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // CERTIFICATE WALLET
    // ─────────────────────────────────────────────────────────────────────

    public function certificates()
    {
        $certificates = LmsCertificateV2::where('user_id', auth()->id())
            ->where('is_revoked', false)
            ->with(['course', 'program', 'template'])
            ->orderByDesc('issued_at')
            ->paginate(12);

        return Inertia::render('LMS/Learn/Certificates', ['certificates' => $certificates]);
    }

    public function verifyCertificate(string $code)
    {
        $cert = LmsCertificateV2::where('unique_code', $code)
            ->with(['user:id,name', 'course:id,title', 'program:id,name'])
            ->firstOrFail();

        return Inertia::render('LMS/Public/VerifyCertificate', [
            'certificate' => $cert,
            'is_valid'    => !$cert->is_revoked,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function buildQuestionSet(LmsQuizConfig $quizConfig)
    {
        if (!empty($quizConfig->question_pool)) {
            $questions = collect();
            foreach ($quizConfig->question_pool as $pool) {
                $poolQuestions = LmsQuestionV2::where('bank_id', $pool['bank_id'] ?? null)
                    ->when($pool['difficulty'] ?? null, fn($q, $d) => $q->where('difficulty', $d))
                    ->inRandomOrder()
                    ->take($pool['count'] ?? 5)
                    ->get();
                $questions = $questions->merge($poolQuestions);
            }
            return $quizConfig->shuffle_questions ? $questions->shuffle() : $questions;
        }

        $questionIds = $quizConfig->question_ids ?? [];
        $limit       = $quizConfig->questions_to_show ?? count($questionIds);
        $questions   = LmsQuestionV2::whereIn('id', $questionIds)->get();

        if ($quizConfig->shuffle_questions) $questions = $questions->shuffle();
        return $questions->take($limit);
    }

    private function sanitizeOptions(array $options): array
    {
        return array_map(fn($opt) => ['id' => $opt['id'] ?? null, 'text' => $opt['text'] ?? '', 'image' => $opt['image'] ?? null], $options);
    }

    private function buildQuizFeedback(LmsQuizAttempt $attempt): array
    {
        return $attempt->responses()->with('question')->get()->map(fn($resp) => [
            'question_id'     => $resp->question_id,
            'selected_answer' => $resp->selected_answer,
            'is_correct'      => $resp->is_correct,
            'score'           => $resp->score,
            'correct_answer'  => $resp->is_correct ? null : ($resp->question->correct_answer ?? null),
            'explanation'     => $resp->question->explanation ?? null,
        ])->toArray();
    }

    private function onVideoCompleted(int $userId, LmsEnrollment $enrollment, LmsVideoLesson $videoLesson): void
    {
        $activity = LmsActivity::find($videoLesson->activity_id);
        if (!$activity) return;

        // Update concept progress video flag
        $cp = LmsConceptProgress::firstOrCreate(
            ['user_id' => $userId, 'enrollment_id' => $enrollment->id, 'concept_id' => $activity->concept_id],
            ['chapter_id' => $activity->chapter_id, 'module_id' => $activity->module_id, 'course_id' => $activity->course_id]
        );
        $cp->update(['video_completed' => true, 'last_activity_at' => now()]);

        // Recalculate progress chain
        $this->progressService->recalculate($userId, $enrollment->id, $activity->concept_id);
        $this->certService->checkAndAward($userId, $enrollment->course_id, $enrollment->id);

        // Award points
        \App\Models\LMS\LmsUserPoints::create([
            'user_id'     => $userId,
            'course_id'   => $enrollment->course_id,
            'action'      => 'video_completed',
            'points'      => 10,
            'description' => "Completed video: {$videoLesson->activity->title}",
        ]);
    }

    public function publicProfile(User $user)
    {
        $user->load(['lmsCertificates.course', 'lmsEnrollments.course']);
        
        $stats = [
            'completed'    => $user->lmsEnrollments->filter(fn($e) => $e->status === 'completed')->count(),
            'certificates' => $user->lmsCertificates->count(),
            'enrolled'     => $user->lmsEnrollments->count(),
        ];

        return Inertia::render('LMS/Public/StudentProfile', [
            'student' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'joined_at' => $user->created_at->format('M Y'),
            ],
            'certificates' => $user->lmsCertificates,
            'stats' => $stats,
        ]);
    }
}
