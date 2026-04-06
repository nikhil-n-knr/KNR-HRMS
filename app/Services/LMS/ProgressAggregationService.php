<?php

namespace App\Services\LMS;

use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsConceptProgress;
use App\Models\LMS\LmsModuleProgress;
use App\Models\LMS\LmsCourseProgress;
use App\Models\LMS\LmsConcept;
use App\Models\LMS\LmsModule;
use App\Models\LmsCourse;
use Illuminate\Support\Facades\DB;

/**
 * ProgressAggregationService
 *
 * Called after EVERY activity completion event to roll up:
 * concept_progress → module_progress → course_progress
 *
 * This is the backbone of all certificate eligibility checks.
 */
class ProgressAggregationService
{
    /**
     * Main entry point — call after any learning event (video completed,
     * quiz passed, assignment graded, reading finished, live session attended).
     */
    public function recalculate(int $userId, int $enrollmentId, int $conceptId): void
    {
        DB::transaction(function () use ($userId, $enrollmentId, $conceptId) {
            $enrollment = LmsEnrollment::findOrFail($enrollmentId);

            // 1. Recompute concept-level progress
            $conceptProgress = $this->recalculateConcept($userId, $enrollmentId, $conceptId);

            // 2. Roll up to module level
            $this->recalculateModule($userId, $enrollmentId, $conceptProgress->module_id, $enrollment->course_id);

            // 3. Roll up to course level
            $this->recalculateCourse($userId, $enrollmentId, $enrollment->course_id);
        });
    }

    /**
     * Maintenance entry point to refresh an entire course enrollment's progress.
     */
    public function aggregateCourseProgress(LmsEnrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {
            // Recalculate all modules for this enrollment
            $modules = LmsModule::where('course_id', $enrollment->course_id)->get();
            foreach ($modules as $module) {
                $this->recalculateModule($enrollment->user_id, $enrollment->id, $module->id, $enrollment->course_id);
            }

            // Finally, roll up to course
            $this->recalculateCourse($enrollment->user_id, $enrollment->id, $enrollment->course_id);
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONCEPT LEVEL
    // ─────────────────────────────────────────────────────────────────────

    public function recalculateConcept(int $userId, int $enrollmentId, int $conceptId): LmsConceptProgress
    {
        $concept = LmsConcept::with('activities.videoLesson', 'activities.quizConfig')->findOrFail($conceptId);
        $criteria = $concept->getCompletionCriteriaDefaulted();

        $progress = LmsConceptProgress::firstOrCreate(
            ['user_id' => $userId, 'enrollment_id' => $enrollmentId, 'concept_id' => $conceptId],
            [
                'chapter_id' => $concept->chapter_id,
                'module_id'  => $concept->module_id,
                'course_id'  => $concept->course_id,
                'status'     => 'in_progress',
            ]
        );

        // Aggregate video watch seconds
        $totalVideoSeconds = DB::table('lms_video_sessions')
            ->join('lms_activities', 'lms_activities.id', '=', 'lms_video_sessions.activity_id')
            ->where('lms_video_sessions.user_id', $userId)
            ->where('lms_activities.concept_id', $conceptId)
            ->sum('lms_video_sessions.total_watch_seconds');

        // Check video completion
        $videoCompleted = DB::table('lms_video_sessions')
            ->join('lms_activities', 'lms_activities.id', '=', 'lms_video_sessions.activity_id')
            ->where('lms_video_sessions.user_id', $userId)
            ->where('lms_activities.concept_id', $conceptId)
            ->where('lms_video_sessions.is_completed', true)
            ->exists();

        // Check quiz pass
        $quizPassed = false;
        $quizBestScore = null;
        if ($criteria['quiz_required']) {
            $quizActivity = $concept->activities()->where('type', 'quiz')->first();
            if ($quizActivity && $quizActivity->quizConfig) {
                $bestAttempt = DB::table('lms_quiz_attempts')
                    ->where('quiz_config_id', $quizActivity->quizConfig->id)
                    ->where('user_id', $userId)
                    ->where('status', 'submitted')
                    ->orderByDesc('percentage')
                    ->first();
                $quizBestScore = $bestAttempt->percentage ?? null;
                $quizPassed = $bestAttempt && $quizBestScore >= $criteria['min_quiz_score'];
            }
        } else {
            $quizPassed = true; // not required, so treated as passed
        }

        // Check assignment
        $assignmentSubmitted = false;
        $assignmentApproved  = false;
        if ($criteria['assignment_required'] ?? false) {
            $assignmentActivity = $concept->activities()->where('type', 'assignment')->first();
            if ($assignmentActivity) {
                $submission = DB::table('lms_assignment_submissions')
                    ->where('user_id', $userId)
                    ->where('activity_id', $assignmentActivity->id)
                    ->orderByDesc('submission_number')
                    ->first();
                $assignmentSubmitted = $submission !== null;
                $assignmentApproved  = $submission && in_array($submission->status, ['graded']) && ($submission->score ?? 0) >= ($assignmentActivity->assignment->pass_mark ?? 60);
            }
        } else {
            $assignmentSubmitted = true;
            $assignmentApproved  = true;
        }

        // Compute overall completion criteria satisfaction
        $checks = [
            !$criteria['video_required'] || $videoCompleted,
            !($criteria['quiz_required'] ?? false) || $quizPassed,
            !($criteria['assignment_required'] ?? false) || $assignmentApproved,
        ];
        $allPassed = !in_array(false, $checks);

        // Compute completion % (weighted)
        $pct = $this->computeConceptCompletionPct($criteria, $videoCompleted, $quizPassed, $assignmentApproved);

        $progress->update([
            'video_watch_seconds'  => $totalVideoSeconds,
            'video_completed'      => $videoCompleted,
            'quiz_passed'          => $quizPassed,
            'quiz_best_score'      => $quizBestScore,
            'assignment_submitted' => $assignmentSubmitted,
            'assignment_approved'  => $assignmentApproved,
            'completion_pct'       => $pct,
            'status'               => $allPassed ? 'completed' : 'in_progress',
            'completed_at'         => $allPassed && !$progress->completed_at ? now() : $progress->completed_at,
            'last_activity_at'     => now(),
        ]);

        return $progress->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────
    // MODULE LEVEL
    // ─────────────────────────────────────────────────────────────────────

    public function recalculateModule(int $userId, int $enrollmentId, int $moduleId, int $courseId): LmsModuleProgress
    {
        $module = LmsModule::findOrFail($moduleId);
        $totalConcepts     = $module->concepts()->where('is_active', true)->count();
        $mandatoryConcepts = $module->concepts()->where('is_mandatory', true)->count();

        $completedConcepts = LmsConceptProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollmentId,
            'module_id'     => $moduleId,
            'status'        => 'completed',
        ])->count();

        $completedMandatory = LmsConceptProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollmentId,
            'module_id'     => $moduleId,
            'status'        => 'completed',
        ])
        ->whereHas('concept', fn($q) => $q->where('is_mandatory', true))
        ->count();

        // Time and quiz scores
        $totalSeconds = LmsConceptProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollmentId,
            'module_id'     => $moduleId,
        ])->sum('time_spent_seconds');

        $avgQuizScore = LmsConceptProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollmentId,
            'module_id'     => $moduleId,
        ])->whereNotNull('quiz_best_score')->avg('quiz_best_score');

        $completionPct = $totalConcepts > 0
            ? round(($completedConcepts / $totalConcepts) * 100, 2)
            : 0;

        $isCompleted = $mandatoryConcepts > 0
            ? $completedMandatory >= $mandatoryConcepts
            : $completedConcepts >= $totalConcepts;

        $mp = LmsModuleProgress::updateOrCreate(
            ['user_id' => $userId, 'enrollment_id' => $enrollmentId, 'module_id' => $moduleId],
            [
                'course_id'          => $courseId,
                'completion_pct'     => $completionPct,
                'concepts_total'     => $totalConcepts,
                'concepts_completed' => $completedConcepts,
                'total_time_seconds' => $totalSeconds,
                'avg_quiz_score'     => $avgQuizScore ? round($avgQuizScore, 2) : null,
                'is_completed'       => $isCompleted,
                'completed_at'       => $isCompleted ? (now()) : null,
            ]
        );

        return $mp;
    }

    // ─────────────────────────────────────────────────────────────────────
    // COURSE LEVEL
    // ─────────────────────────────────────────────────────────────────────

    public function recalculateCourse(int $userId, int $enrollmentId, int $courseId): LmsCourseProgress
    {
        $enrollment = LmsEnrollment::findOrFail($enrollmentId);

        $totalModules     = LmsModule::where('course_id', $courseId)->where('is_active', true)->count();
        $mandatoryModules = LmsModule::where('course_id', $courseId)->where('is_mandatory', true)->count();
        $totalConcepts    = LmsConcept::where('course_id', $courseId)->where('is_active', true)->count();

        $completedModules = LmsModuleProgress::where([
            'user_id' => $userId, 'enrollment_id' => $enrollmentId, 'course_id' => $courseId,
        ])->where('is_completed', true)->count();

        $completedConcepts = LmsConceptProgress::where([
            'user_id' => $userId, 'enrollment_id' => $enrollmentId, 'course_id' => $courseId,
        ])->where('status', 'completed')->count();

        $totalWatchSeconds = LmsConceptProgress::where([
            'user_id' => $userId, 'enrollment_id' => $enrollmentId, 'course_id' => $courseId,
        ])->sum('video_watch_seconds');

        $totalTimeSeconds = LmsConceptProgress::where([
            'user_id' => $userId, 'enrollment_id' => $enrollmentId, 'course_id' => $courseId,
        ])->sum('time_spent_seconds');

        $avgQuizScore = LmsConceptProgress::where([
            'user_id' => $userId, 'enrollment_id' => $enrollmentId, 'course_id' => $courseId,
        ])->whereNotNull('quiz_best_score')->avg('quiz_best_score');

        $assignmentsSubmitted = LmsConceptProgress::where([
            'user_id' => $userId, 'enrollment_id' => $enrollmentId, 'course_id' => $courseId,
        ])->where('assignment_submitted', true)->count();

        $liveSessionsAttended = DB::table('lms_live_attendance')
            ->join('lms_live_sessions', 'lms_live_sessions.id', '=', 'lms_live_attendance.session_id')
            ->where('lms_live_attendance.user_id', $userId)
            ->where('lms_live_sessions.course_id', $courseId)
            ->where('lms_live_attendance.is_counted', true)
            ->count();

        $completionPct = $totalConcepts > 0
            ? round(($completedConcepts / $totalConcepts) * 100, 2)
            : 0;

        $isCompleted = $mandatoryModules > 0
            ? $completedModules >= $mandatoryModules
            : $completedModules >= $totalModules;

        $cp = LmsCourseProgress::updateOrCreate(
            ['user_id' => $userId, 'enrollment_id' => $enrollmentId, 'course_id' => $courseId],
            [
                'completion_pct'        => $completionPct,
                'modules_total'         => $totalModules,
                'modules_completed'     => $completedModules,
                'concepts_total'        => $totalConcepts,
                'concepts_completed'    => $completedConcepts,
                'total_watch_seconds'   => $totalWatchSeconds,
                'total_time_seconds'    => $totalTimeSeconds,
                'avg_quiz_score'        => $avgQuizScore ? round($avgQuizScore, 2) : null,
                'assignments_submitted' => $assignmentsSubmitted,
                'live_sessions_attended'=> $liveSessionsAttended,
                'is_completed'          => $isCompleted,
                'completed_at'          => $isCompleted ? now() : null,
                'last_activity_at'      => now(),
            ]
        );

        // Update enrollment status
        if ($isCompleted && $enrollment->status !== 'completed') {
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return $cp;
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function computeConceptCompletionPct(
        array $criteria,
        bool $videoCompleted,
        bool $quizPassed,
        bool $assignmentApproved
    ): float {
        $weights = [];
        $scores  = [];

        if ($criteria['video_required'] ?? true) {
            $weights[] = 40; $scores[] = $videoCompleted ? 40 : 0;
        }
        if ($criteria['quiz_required'] ?? false) {
            $weights[] = 40; $scores[] = $quizPassed ? 40 : 0;
        }
        if ($criteria['assignment_required'] ?? false) {
            $weights[] = 20; $scores[] = $assignmentApproved ? 20 : 0;
        }

        $totalWeight = array_sum($weights);
        if ($totalWeight === 0) return 0;

        return round((array_sum($scores) / $totalWeight) * 100, 2);
    }
}
