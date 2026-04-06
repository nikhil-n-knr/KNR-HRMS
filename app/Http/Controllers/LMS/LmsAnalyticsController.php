<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use App\Models\LMS\LmsInstitution;
use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsCourseProgress;
use App\Models\LMS\LmsConceptProgress;
use App\Models\LMS\LmsModuleProgress;
use App\Models\LMS\LmsQuizAttempt;
use App\Models\LMS\LmsCertificateV2;
use App\Models\LMS\LmsLiveSession;
use App\Models\LmsCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LmsAnalyticsController extends Controller
{
    /**
     * State-level command center dashboard
     * Drill: State → District → Board → University → College → Department → Course → Student
     */
    public function commandCenter(Request $request)
    {
        $institutionId = $request->institution_id;
        $institution   = $institutionId ? LmsInstitution::find($institutionId) : null;

        // Get institution scope (all descendant IDs if institution selected)
        $institutionIds = $institution
            ? $institution->getDescendantIds()
            : LmsInstitution::active()->pluck('id')->toArray();

        // KPI cards
        $kpis = $this->getKpis($institutionIds);

        // Course performance heatmap (which topics fail most)
        $topicHeatmap = $this->getTopicHeatmap($institutionIds);

        // Dropout funnel (% of enrolled who dropped at each module)
        $dropoutFunnel = $this->getDropoutFunnel($institutionIds);

        // College comparison table
        $collegeComparison = $this->getCollegeComparison($institutionIds);

        // Certificate trends
        $certTrends = $this->getCertificateTrends($institutionIds);

        // Daily active learners (last 30 days)
        $activeLearnersChart = $this->getActiveLearnersTrend($institutionIds);

        // Top performing colleges
        $topColleges = $this->getTopColleges($institutionIds, 10);

        // Institution tree for drill-down nav
        $institutionTree = LmsInstitution::whereNull('parent_id')->with('allChildren')->get();

        return Inertia::render('LMS/Admin/Analytics/CommandCenter', [
            'kpis'                 => $kpis,
            'topic_heatmap'        => $topicHeatmap,
            'dropout_funnel'       => $dropoutFunnel,
            'college_comparison'   => $collegeComparison,
            'cert_trends'          => $certTrends,
            'active_learners_chart'=> $activeLearnersChart,
            'top_colleges'         => $topColleges,
            'institution_tree'     => $institutionTree,
            'selected_institution' => $institution,
        ]);
    }

    /**
     * Per-course analytics (faculty/admin view)
     */
    public function courseAnalytics(LmsCourse $course)
    {
        $course->load('modules.chapters.concepts');

        // Enrollment stats
        $enrollmentStats = [
            'total'    => LmsEnrollment::where('course_id', $course->id)->count(),
            'active'   => LmsEnrollment::where('course_id', $course->id)->where('status', 'active')->count(),
            'completed'=> LmsEnrollment::where('course_id', $course->id)->where('status', 'completed')->count(),
            'dropped'  => LmsEnrollment::where('course_id', $course->id)->where('status', 'dropped')->count(),
        ];

        // Completion rate over time
        $completionTrend = DB::table('lms_course_progress')
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->selectRaw("DATE(completed_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Per-module completion rates
        $moduleStats = $course->modules()->ordered()->get()->map(function ($module) {
            $totalEnrolled = LmsEnrollment::where('course_id', $module->course_id)->where('status', 'active')->count();
            $completedModule = DB::table('lms_module_progress')
                ->where('module_id', $module->id)
                ->where('is_completed', true)
                ->count();
            $avgTime = DB::table('lms_module_progress')->where('module_id', $module->id)->avg('total_time_seconds');
            $avgQuiz = DB::table('lms_concept_progress')
                ->where('module_id', $module->id)
                ->whereNotNull('quiz_best_score')
                ->avg('quiz_best_score');

            return [
                'module_id'        => $module->id,
                'title'            => $module->title,
                'completion_rate'  => $totalEnrolled > 0 ? round($completedModule / $totalEnrolled * 100, 1) : 0,
                'avg_time_minutes' => round($avgTime / 60, 1),
                'avg_quiz_score'   => round($avgQuiz ?? 0, 1),
            ];
        });

        // Stuck points (concepts where most students fail)
        $stuckConcepts = DB::table('lms_concept_progress')
            ->join('lms_concepts', 'lms_concepts.id', '=', 'lms_concept_progress.concept_id')
            ->where('lms_concepts.course_id', $course->id)
            ->selectRaw("
                lms_concepts.id,
                lms_concepts.title,
                AVG(lms_concept_progress.quiz_best_score) as avg_quiz_score,
                SUM(CASE WHEN lms_concept_progress.status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as completion_rate,
                COUNT(*) as learner_count
            ")
            ->groupBy('lms_concepts.id', 'lms_concepts.title')
            ->having('learner_count', '>', 5)
            ->orderBy('completion_rate')
            ->limit(10)
            ->get();

        // Quiz performance distribution
        $quizDistribution = DB::table('lms_quiz_attempts')
            ->join('lms_activities', 'lms_activities.id', '=', 'lms_quiz_attempts.activity_id')
            ->where('lms_activities.course_id', $course->id)
            ->where('lms_quiz_attempts.status', 'submitted')
            ->selectRaw("
                CASE
                    WHEN percentage < 40 THEN 'Below 40%'
                    WHEN percentage < 60 THEN '40-60%'
                    WHEN percentage < 80 THEN '60-80%'
                    ELSE 'Above 80%'
                END as bracket,
                COUNT(*) as count
            ")
            ->groupBy('bracket')
            ->get();

        // Live session stats
        $liveStats = DB::table('lms_live_sessions')
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->selectRaw("COUNT(*) as total_sessions, AVG(peak_attendees) as avg_attendees, AVG(actual_duration_minutes) as avg_duration")
            ->first();

        return Inertia::render('LMS/Admin/Analytics/CourseAnalytics', [
            'course'            => $course,
            'enrollment_stats'  => $enrollmentStats,
            'completion_trend'  => $completionTrend,
            'module_stats'      => $moduleStats,
            'stuck_concepts'    => $stuckConcepts,
            'quiz_distribution' => $quizDistribution,
            'live_stats'        => $liveStats,
        ]);
    }

    /**
     * Institution-level report
     */
    public function institutionReport(LmsInstitution $institution)
    {
        $desc = $institution->getDescendantIds();

        $report = [
            'institution'      => $institution,
            'total_learners'   => DB::table('lms_enrollments')->whereIn('institution_id', $desc)->distinct('user_id')->count('user_id'),
            'total_enrollments'=> LmsEnrollment::whereIn('institution_id', $desc)->count(),
            'completion_rate'  => DB::table('lms_course_progress')
                ->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_course_progress.enrollment_id')
                ->whereIn('lms_enrollments.institution_id', $desc)
                ->avg('lms_course_progress.completion_pct'),
            'certificates_issued' => LmsCertificateV2::whereHas('enrollment', fn($q) => $q->whereIn('institution_id', $desc))->count(),
            'courses'          => DB::table('lms_courses')->whereIn('institution_id', $desc)->select('id','title','enrolled_count','completion_count','avg_rating')->get(),
        ];

        return response()->json($report);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function getKpis(array $institutionIds): array
    {
        return [
            'total_learners' => DB::table('lms_enrollments')
                ->when($institutionIds, fn($q) => $q->whereIn('institution_id', $institutionIds))
                ->distinct('user_id')->count('user_id'),
            'active_enrollments' => LmsEnrollment::when($institutionIds, fn($q) => $q->whereIn('institution_id', $institutionIds))
                ->where('status', 'active')->count(),
            'certificates_issued' => LmsCertificateV2::when($institutionIds, fn($q) =>
                $q->whereHas('enrollment', fn($eq) => $eq->whereIn('institution_id', $institutionIds)))
                ->where('is_revoked', false)->count(),
            'avg_completion_pct' => DB::table('lms_course_progress')
                ->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_course_progress.enrollment_id')
                ->when($institutionIds, fn($q) => $q->whereIn('lms_enrollments.institution_id', $institutionIds))
                ->avg('lms_course_progress.completion_pct'),
            'total_watch_hours'  => DB::table('lms_video_sessions')
                ->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_video_sessions.enrollment_id')
                ->when($institutionIds, fn($q) => $q->whereIn('lms_enrollments.institution_id', $institutionIds))
                ->sum('lms_video_sessions.total_watch_seconds') / 3600,
            'live_sessions_held' => LmsLiveSession::where('status', 'completed')
                ->join('lms_courses', 'lms_courses.id', '=', 'lms_live_sessions.course_id')
                ->when($institutionIds, fn($q) => $q->whereIn('lms_courses.institution_id', $institutionIds))
                ->count(),
        ];
    }

    private function getTopicHeatmap(array $institutionIds): array
    {
        return DB::table('lms_concept_progress')
            ->join('lms_concepts', 'lms_concepts.id', '=', 'lms_concept_progress.concept_id')
            ->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_concept_progress.enrollment_id')
            ->when($institutionIds, fn($q) => $q->whereIn('lms_enrollments.institution_id', $institutionIds))
            ->selectRaw("
                lms_concepts.id,
                lms_concepts.title,
                lms_concepts.course_id,
                AVG(lms_concept_progress.quiz_best_score) as avg_quiz_score,
                SUM(CASE WHEN lms_concept_progress.status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as completion_rate,
                COUNT(*) as learner_count
            ")
            ->groupBy('lms_concepts.id', 'lms_concepts.title', 'lms_concepts.course_id')
            ->having('learner_count', '>', 10)
            ->orderBy('completion_rate')
            ->limit(20)
            ->get()
            ->toArray();
    }

    private function getDropoutFunnel(array $institutionIds): array
    {
        // Returns per-module completion rate to show where learners drop
        return DB::table('lms_module_progress')
            ->join('lms_modules', 'lms_modules.id', '=', 'lms_module_progress.module_id')
            ->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_module_progress.enrollment_id')
            ->when($institutionIds, fn($q) => $q->whereIn('lms_enrollments.institution_id', $institutionIds))
            ->selectRaw("
                lms_modules.title,
                lms_modules.sort_order,
                AVG(lms_module_progress.completion_pct) as avg_completion,
                SUM(CASE WHEN lms_module_progress.is_completed THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as completion_rate
            ")
            ->groupBy('lms_modules.id', 'lms_modules.title', 'lms_modules.sort_order')
            ->orderBy('lms_modules.sort_order')
            ->get()
            ->toArray();
    }

    private function getCollegeComparison(array $institutionIds): array
    {
        return LmsInstitution::whereIn('id', $institutionIds)
            ->where('type', 'college')
            ->withCount(['courses'])
            ->get()
            ->map(function ($inst) {
                $instIds = [$inst->id];
                return [
                    'institution_id'   => $inst->id,
                    'name'             => $inst->name,
                    'enrolled_count'   => LmsEnrollment::whereIn('institution_id', $instIds)->distinct('user_id')->count('user_id'),
                    'avg_completion'   => DB::table('lms_course_progress')->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_course_progress.enrollment_id')->whereIn('lms_enrollments.institution_id', $instIds)->avg('completion_pct'),
                    'cert_count'       => LmsCertificateV2::whereHas('enrollment', fn($q) => $q->whereIn('institution_id', $instIds))->count(),
                ];
            })
            ->sortByDesc('avg_completion')
            ->values()
            ->toArray();
    }

    private function getCertificateTrends(array $institutionIds): array
    {
        return DB::table('lms_certificates_v2')
            ->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_certificates_v2.enrollment_id')
            ->when($institutionIds, fn($q) => $q->whereIn('lms_enrollments.institution_id', $institutionIds))
            ->where('is_revoked', false)
            ->selectRaw("DATE_FORMAT(issued_at, '%Y-%m') as month, type, COUNT(*) as count")
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    private function getActiveLearnersTrend(array $institutionIds): array
    {
        return DB::table('lms_concept_progress')
            ->join('lms_enrollments', 'lms_enrollments.id', '=', 'lms_concept_progress.enrollment_id')
            ->when($institutionIds, fn($q) => $q->whereIn('lms_enrollments.institution_id', $institutionIds))
            ->where('lms_concept_progress.last_activity_at', '>=', now()->subDays(30))
            ->selectRaw("DATE(lms_concept_progress.last_activity_at) as date, COUNT(DISTINCT lms_concept_progress.user_id) as active_learners")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getTopColleges(array $institutionIds, int $limit): array
    {
        return LmsInstitution::whereIn('id', $institutionIds)
            ->where('type', 'college')
            ->limit($limit)
            ->get()
            ->map(fn($inst) => ['id' => $inst->id, 'name' => $inst->name])
            ->toArray();
    }
}
