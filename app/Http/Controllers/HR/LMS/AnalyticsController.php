<?php

namespace App\Http\Controllers\HR\LMS;

use App\Http\Controllers\Controller;
use App\Services\LMS\AnalyticsService;
use App\Models\LmsCourse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}
    
    /**
     * Show analytics dashboard
     */
    public function index(Request $request)
    {
        // Authorization handled by route middleware
        
        $complianceMatrix = $this->analyticsService->getComplianceMatrix();
        $defaulters = $this->analyticsService->getDefaulters();
        
        return Inertia::render('HR/LMS/Analytics', [
            'compliance_matrix' => $complianceMatrix,
            'defaulters' => $defaulters
        ]);
    }
    
    /**
     * Get course-specific analytics
     */
    public function course(LmsCourse $course)
    {
        // Authorization handled by route middleware
        
        $analytics = $this->analyticsService->getCourseAnalytics($course->id);
        
        return Inertia::render('HR/LMS/CourseAnalytics', [
            'analytics' => $analytics
        ]);
    }
    
    /**
     * Export compliance report
     */
    public function export(Request $request)
    {
        // Authorization handled by route middleware
        
        $complianceMatrix = $this->analyticsService->getComplianceMatrix();
        
        // Generate CSV
        $csv = "Department," . implode(',', $complianceMatrix['courses']->pluck('title')->toArray()) . "\n";
        
        foreach ($complianceMatrix['matrix'] as $row) {
            $csv .= $row['department'] . ',';
            $csv .= implode(',', array_map(fn($c) => $c['completion_rate'] . '%', $row['courses']));
            $csv .= "\n";
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="lms_compliance_' . date('Y-m-d') . '.csv"');
    }
}
