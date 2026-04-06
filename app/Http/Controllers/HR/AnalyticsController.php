<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Services\Analytics\CompensationAnalyticsService;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class AnalyticsController extends Controller
{
    protected $strategicService;

    public function __construct(
        CompensationAnalyticsService $analyticsService, 
        \App\Services\Analytics\StrategicAnalyticsService $strategicService
    )
    {
        $this->analyticsService = $analyticsService;
        $this->strategicService = $strategicService;
    }

    /**
     * HR Command Center (Dashboard).
     * Accessible by Admin / HR Head / VP (Limited).
     */
    public function commandCenter(Request $request)
    {
        // Strict Access Control
        if (!auth()->user()->hasRole(['Admin', 'HR Manager', 'Super Admin'])) {
            abort(403, 'Restricted Access: HR Command Center.');
        }

        $deptId = $request->department_id; // For filtering

        return Inertia::render('HR/Analytics/CommandCenter', [
            'bellCurve' => $this->analyticsService->getBellCurve($deptId),
            'payVsPerf' => $this->analyticsService->getPayVsPerformance($deptId),
            'budgetHeatmap' => $this->analyticsService->getBudgetHeatmap(),
            'filters' => $request->only(['department_id'])
        ]);
    }
    
    /**
     * Strategic Compensation Intelligence (BI Layer).
     */
    public function strategicIndex(Request $request)
    {
         if (!auth()->user()->hasRole(['Admin', 'HR Manager', 'Super Admin'])) abort(403);
         
         return Inertia::render('HR/Analytics/StrategicIndex', [
             'matrix' => $this->strategicService->getMatrixData(),
             'structure' => $this->strategicService->getStructureData(),
             'vintage' => $this->strategicService->getVintageData(),
             'timeMachine' => $this->strategicService->getTimeMachineData(),
         ]);
    }

    public function playerSearch(Request $request)
    {
        $query = $request->input('query');
        if (strlen($query) < 2) return response()->json([]);

        $employees = Employee::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('employee_code', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'designation', 'employee_code']); // Light payload

        return response()->json($employees);
    }

    /**
     * Individual Career DNA (Growth Timeline).
     * Accessible by Manager (for direct reports) or Admin.
     */
    public function careerDna(Employee $employee)
    {
        // Check Policy (Manager of Employee OR Admin)
        // Gate::authorize('view_analytics', $employee); 

        $dna = $this->analyticsService->getCareerDna($employee->id);

        return response()->json($dna);
    }
}
