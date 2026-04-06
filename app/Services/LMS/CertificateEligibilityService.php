<?php

namespace App\Services\LMS;

use App\Models\LMS\LmsCertificateRule;
use App\Models\LMS\LmsCertificateV2;
use App\Models\LMS\LmsCourseProgress;
use App\Models\LMS\LmsConceptProgress;
use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsLiveAttendance;
use App\Models\LmsCourse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * CertificateEligibilityService
 *
 * Evaluates all certificate rule conditions and auto-issues certificates.
 * Called after every progress recalculation.
 *
 * Supports:
 *  - Topic-level micro-certificates
 *  - Module certificates
 *  - Course certificates
 *  - Master certificates (N-of-M topics rule)
 */
class CertificateEligibilityService
{
    // PDF generation is handled by a queued job (GenerateCertificatePdf)
    // No constructor dependencies needed here

    /**
     * Entry point: check and award any certificates the user now qualifies for.
     */
    public function checkAndAward(int $userId, int $courseId, ?int $enrollmentId = null): array
    {
        $awarded = [];

        // Check course-level certificate rules first
        $enrollment = $enrollmentId
            ? LmsEnrollment::find($enrollmentId)
            : LmsEnrollment::where(['user_id' => $userId, 'course_id' => $courseId])->first();

        if (!$enrollment) return $awarded;

        $rules = LmsCertificateRule::where(function ($q) use ($courseId) {
            $q->where('course_id', $courseId)
              ->orWhereNull('course_id');
        })
        ->where('is_active', true)
        ->get();

        foreach ($rules as $rule) {
            // Skip if already issued (and not revoked)
            if ($this->alreadyIssued($userId, $rule->id)) continue;

            if ($this->meetsRuleConditions($userId, $enrollment, $rule)) {
                $cert = $this->issueCertificate($userId, $enrollment, $rule);
                $awarded[] = $cert;
            }
        }

        // Check master certificate for program
        if ($enrollment->program_id) {
            $masterCerts = $this->checkMasterCertificate($userId, $enrollment);
            $awarded = array_merge($awarded, $masterCerts);
        }

        return $awarded;
    }

    // ─────────────────────────────────────────────────────────────────────
    // RULE EVALUATION
    // ─────────────────────────────────────────────────────────────────────

    public function meetsRuleConditions(int $userId, LmsEnrollment $enrollment, LmsCertificateRule $rule): bool
    {
        $courseProgress = LmsCourseProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollment->id,
            'course_id'     => $enrollment->course_id,
        ])->first();

        if (!$courseProgress) return false;

        // 1. Minimum course completion %
        if ($courseProgress->completion_pct < $rule->min_completion_pct) {
            return false;
        }

        // 2. Minimum total watch time
        if ($rule->min_watch_seconds > 0 && $courseProgress->total_watch_seconds < $rule->min_watch_seconds) {
            return false;
        }

        // 3. Minimum average quiz score
        if ($rule->min_quiz_avg_score !== null) {
            if (($courseProgress->avg_quiz_score ?? 0) < $rule->min_quiz_avg_score) {
                return false;
            }
        }

        // 4. Live session attendance
        if ($rule->min_live_sessions !== null) {
            if ($courseProgress->live_sessions_attended < $rule->min_live_sessions) {
                return false;
            }
        }

        // 5. Topic requirements (N-of-M rule — e.g. 45 of 50 topics)
        if (!empty($rule->topic_requirements)) {
            if (!$this->checkTopicRequirements($userId, $enrollment, $rule->topic_requirements)) {
                return false;
            }
        }

        // 6. Capstone project required
        if ($rule->capstone_required) {
            if (!$this->checkCapstoneCompleted($userId, $enrollment->course_id)) {
                return false;
            }
        }

        // 7. Custom rules (extensible JSON rule blocks)
        if (!empty($rule->custom_rules)) {
            if (!$this->evaluateCustomRules($userId, $enrollment, $rule->custom_rules)) {
                return false;
            }
        }

        return true;
    }

    /**
     * N-of-M topic requirement check.
     * Example rule: {"min_topics_completed": 45, "total_topics": 50}
     */
    private function checkTopicRequirements(int $userId, LmsEnrollment $enrollment, array $requirements): bool
    {
        $minCompleted = $requirements['min_topics_completed'] ?? null;
        $totalTopics  = $requirements['total_topics'] ?? null;
        $specificModuleIds = $requirements['module_ids'] ?? null;

        if ($minCompleted === null) return true;

        $query = DB::table('lms_module_progress')->where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollment->id,
            'course_id'     => $enrollment->course_id,
            'is_completed'  => true,
        ]);

        if ($specificModuleIds) {
            $query->whereIn('module_id', $specificModuleIds);
        }

        $completedCount = $query->count();

        return $completedCount >= $minCompleted;
    }

    /**
     * Check if capstone assignment is submitted and graded/approved
     */
    private function checkCapstoneCompleted(int $userId, int $courseId): bool
    {
        // Find activities tagged as capstone
        return DB::table('lms_assignment_submissions')
            ->join('lms_activities', 'lms_activities.id', '=', 'lms_assignment_submissions.activity_id')
            ->where('lms_assignment_submissions.user_id', $userId)
            ->where('lms_activities.course_id', $courseId)
            ->whereJsonContains('lms_activities.config', ['is_capstone' => true])
            ->whereIn('lms_assignment_submissions.status', ['graded'])
            ->exists();
    }

    /**
     * Evaluate custom rule blocks (very flexible JSON engine)
     * Supported rule types: "min_score_in_topic", "min_attendance_per_session", etc.
     */
    private function evaluateCustomRules(int $userId, LmsEnrollment $enrollment, array $customRules): bool
    {
        foreach ($customRules as $rule) {
            switch ($rule['type'] ?? '') {
                case 'min_score_in_module':
                    $moduleId = $rule['module_id'];
                    $minScore = $rule['min_score'];
                    $avgScore = LmsConceptProgress::where([
                        'user_id'       => $userId,
                        'enrollment_id' => $enrollment->id,
                        'module_id'     => $moduleId,
                    ])->avg('quiz_best_score');
                    if (($avgScore ?? 0) < $minScore) return false;
                    break;

                case 'min_live_attendance_pct':
                    $minPct = $rule['min_pct'];
                    $avgPct = LmsLiveAttendance::whereHas('session', fn($q) => $q->where('course_id', $enrollment->course_id))
                        ->where('user_id', $userId)
                        ->avg('attendance_pct');
                    if (($avgPct ?? 0) < $minPct) return false;
                    break;
            }
        }
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────
    // MASTER CERTIFICATE (Cross-course / Program level)
    // ─────────────────────────────────────────────────────────────────────

    private function checkMasterCertificate(int $userId, LmsEnrollment $enrollment): array
    {
        $awarded = [];
        $program = $enrollment->program;
        if (!$program || empty($program->certificate_rules)) return $awarded;

        $masterRules = $program->certificate_rules;

        // Check if already issued
        $alreadyIssued = LmsCertificateV2::where([
            'user_id'    => $userId,
            'program_id' => $enrollment->program_id,
            'type'       => 'master',
            'is_revoked' => false,
        ])->exists();

        if ($alreadyIssued) return $awarded;

        // Evaluate all courses in the program
        $programCourseIds = DB::table('lms_course_programs')
            ->where('program_id', $enrollment->program_id)
            ->pluck('course_id')
            ->toArray();

        $completedCourses = LmsCourseProgress::where('user_id', $userId)
            ->whereIn('course_id', $programCourseIds)
            ->where('is_completed', true)
            ->count();

        $totalCourses = count($programCourseIds);

        // Apply master certificate rules from program config
        $minCourses        = $masterRules['min_courses_completed'] ?? $totalCourses;
        $minTotalWatchHours= $masterRules['min_total_watch_hours'] ?? 0;
        $capstoneRequired  = $masterRules['capstone_required'] ?? false;

        if ($completedCourses < $minCourses) return $awarded;

        $totalWatchSeconds = LmsCourseProgress::where('user_id', $userId)
            ->whereIn('course_id', $programCourseIds)
            ->sum('total_watch_seconds');

        if ($totalWatchSeconds < ($minTotalWatchHours * 3600)) return $awarded;

        // Issue master certificate
        $cert = $this->issueMasterCertificate($userId, $program, $enrollment);
        if ($cert) $awarded[] = $cert;

        return $awarded;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ISSUANCE
    // ─────────────────────────────────────────────────────────────────────

    private function issueCertificate(int $userId, LmsEnrollment $enrollment, LmsCertificateRule $rule): LmsCertificateV2
    {
        $user   = \App\Models\User::find($userId);
        $course = LmsCourse::find($enrollment->course_id);

        $uniqueCode = strtoupper(Str::random(8)) . '-' . now()->format('ymd');

        $cert = LmsCertificateV2::create([
            'user_id'     => $userId,
            'rule_id'     => $rule->id,
            'template_id' => $rule->template_id,
            'course_id'   => $enrollment->course_id,
            'program_id'  => $enrollment->program_id,
            'type'        => $rule->type,
            'unique_code' => $uniqueCode,
            'qr_data'     => url('/lms/verify/' . $uniqueCode),
            'issued_at'   => now(),
            'metadata'    => [
                'learner_name'  => $user->name,
                'course_name'   => $course?->title,
                'issued_by'     => config('app.name'),
                'issue_date'    => now()->format('d M Y'),
                'score'         => LmsCourseProgress::where(['user_id' => $userId, 'course_id' => $enrollment->course_id])->value('avg_quiz_score'),
            ],
        ]);

        // Generate PDF asynchronously (dispatch job)
        // dispatch(new \App\Jobs\LMS\GenerateCertificatePdf($cert->id));

        // Award gamification points
        $this->awardCertificatePoints($userId, $enrollment->course_id, $rule->type);

        return $cert;
    }

    private function issueMasterCertificate(int $userId, $program, LmsEnrollment $enrollment): ?LmsCertificateV2
    {
        $user = \App\Models\User::find($userId);
        $uniqueCode = 'MASTER-' . strtoupper(Str::random(10));

        return LmsCertificateV2::create([
            'user_id'    => $userId,
            'program_id' => $program->id,
            'type'       => 'master',
            'unique_code'=> $uniqueCode,
            'qr_data'    => url('/lms/verify/' . $uniqueCode),
            'issued_at'  => now(),
            'metadata'   => [
                'learner_name'  => $user->name,
                'program_name'  => $program->name,
                'degree_type'   => $program->degree_type,
                'institution'   => $program->institution?->name,
                'issue_date'    => now()->format('d M Y'),
                'certificate_type' => 'Master Certification',
            ],
        ]);
    }

    private function alreadyIssued(int $userId, int $ruleId): bool
    {
        return LmsCertificateV2::where([
            'user_id'    => $userId,
            'rule_id'    => $ruleId,
            'is_revoked' => false,
        ])->exists();
    }

    private function awardCertificatePoints(int $userId, int $courseId, string $certType): void
    {
        $points = match ($certType) {
            'topic'   => 50,
            'module'  => 150,
            'course'  => 500,
            'master'  => 2000,
            default   => 100,
        };

        \App\Models\LMS\LmsUserPoints::create([
            'user_id'     => $userId,
            'course_id'   => $courseId,
            'action'      => 'certificate_earned',
            'points'      => $points,
            'description' => "Earned {$certType} certificate",
        ]);
    }

    /**
     * Compute real-time eligibility meter (0–100%) for frontend display
     */
    public function getEligibilityMeter(int $userId, int $enrollmentId, LmsCertificateRule $rule): array
    {
        $enrollment     = LmsEnrollment::find($enrollmentId);
        $courseProgress = LmsCourseProgress::where([
            'user_id'       => $userId,
            'enrollment_id' => $enrollmentId,
        ])->first();

        if (!$courseProgress) {
            return ['overall_pct' => 0, 'checks' => []];
        }

        $checks = [];

        // Completion
        $checks['completion'] = [
            'label'    => 'Course Completion',
            'required' => $rule->min_completion_pct . '%',
            'current'  => round($courseProgress->completion_pct, 1) . '%',
            'passed'   => $courseProgress->completion_pct >= $rule->min_completion_pct,
        ];

        // Watch time
        if ($rule->min_watch_seconds > 0) {
            $hrs = $rule->min_watch_seconds / 3600;
            $checks['watch_time'] = [
                'label'    => 'Min Watch Time',
                'required' => round($hrs, 1) . ' hrs',
                'current'  => round($courseProgress->total_watch_seconds / 3600, 1) . ' hrs',
                'passed'   => $courseProgress->total_watch_seconds >= $rule->min_watch_seconds,
            ];
        }

        // Quiz score
        if ($rule->min_quiz_avg_score !== null) {
            $checks['quiz_score'] = [
                'label'    => 'Min Quiz Score',
                'required' => $rule->min_quiz_avg_score . '%',
                'current'  => round($courseProgress->avg_quiz_score ?? 0, 1) . '%',
                'passed'   => ($courseProgress->avg_quiz_score ?? 0) >= $rule->min_quiz_avg_score,
            ];
        }

        // Topic N-of-M
        if (!empty($rule->topic_requirements)) {
            $minTopics = $rule->topic_requirements['min_topics_completed'] ?? 0;
            $completedTopics = DB::table('lms_module_progress')->where([
                'user_id'       => $userId,
                'enrollment_id' => $enrollmentId,
                'is_completed'  => true,
            ])->count();
            $checks['topics'] = [
                'label'    => 'Topics Completed',
                'required' => $minTopics . ' topics',
                'current'  => $completedTopics . ' topics',
                'passed'   => $completedTopics >= $minTopics,
            ];
        }

        $passedCount = count(array_filter($checks, fn($c) => $c['passed']));
        $overall = count($checks) > 0 ? round(($passedCount / count($checks)) * 100) : 0;

        return [
            'overall_pct' => $overall,
            'checks'       => $checks,
            'can_issue'    => $passedCount === count($checks),
        ];
    }
}
