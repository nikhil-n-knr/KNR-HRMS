<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsInstitution;
use App\Models\LMS\LmsPlan;
use App\Models\LMS\LmsSubscription;
use App\Models\LMS\LmsTransaction;
use App\Models\LmsCourse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LmsHubController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->section ?? 'dashboard';
        $tab     = $request->tab     ?? 'overview';

        return Inertia::render('LMS/Admin/Hub', [
            'initialSection' => $section,
            'initialTab'     => $tab,
            'hubStats' => [
                'global_kpis'     => $this->getGlobalKpis(),
                'section_metrics' => $this->getSectionMetrics($section, $tab),
                'popular_courses' => LmsCourse::with(['category', 'creator'])->orderByDesc('enrolled_count')->take(10)->get(),
            ],
            // Data for specific sections
            'courses'      => $section === 'courses' ? $this->getCoursesData($request) : [],
            'learners'     => $section === 'learners' ? $this->getLearnersData($request) : [],
            'institutions' => $section === 'institutions' ? $this->getInstitutionsData($request) : [],
            'plans'        => $section === 'config' ? LmsPlan::all() : [],
        ]);
    }

    private function getGlobalKpis()
    {
        return [
            'total_revenue'    => LmsTransaction::successful()->sum('amount'),
            'active_learners'  => LmsEnrollment::active()->distinct('user_id')->count('user_id'),
            'course_count'     => LmsCourse::count(),
            'completion_rate'  => DB::table('lms_course_progress')->avg('completion_pct') ?? 0,
            'revenue_growth'   => 12.5, // Mock for now
        ];
    }

    private function getSectionMetrics($section, $tab)
    {
        // Add specific logic for each section/tab metrics
        return [
            'daily_enrollments' => LmsEnrollment::where('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')->get(),
            'popular_courses'   => LmsCourse::orderByDesc('enrolled_count')->take(5)->get(),
        ];
    }

    private function getCoursesData(Request $request)
    {
        return LmsCourse::with(['category', 'creator'])
            ->withCount(['modules', 'enrollments'])
            ->latest()
            ->paginate(20);
    }

    private function getLearnersData(Request $request)
    {
        return User::whereHas('lmsEnrollments')
            ->with(['lmsEnrollments.course', 'lmsSubscriptions.plan'])
            ->withCount(['lmsEnrollments', 'lmsCertificates'])
            ->latest()
            ->paginate(20);
    }

    private function getInstitutionsData(Request $request)
    {
        return LmsInstitution::withCount(['courses', 'enrollments'])
            ->whereNull('parent_id')
            ->get();
    }
}
