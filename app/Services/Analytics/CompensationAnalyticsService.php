<?php

namespace App\Services\Analytics;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\PerformanceReview; // Assuming this model exists or we key off Rating
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompensationAnalyticsService
{
    /**
     * Get the growth timeline for a specific employee.
     * "Career DNA"
     */
    public function getCareerDna(int $employeeId)
    {
        $salaries = EmployeeSalary::where('employee_id', $employeeId)
            ->where('is_active', true) // Or all? We need history.
            ->orWhere(function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->orderBy('effective_date', 'asc')
            ->get();

        // If no history (or only 1 record), we can't show much growth.
        // We filter for distinct changes in CTC or Designation.
        
        $timeline = $salaries->map(function ($salary, $index) use ($salaries) {
             $prev = $index > 0 ? $salaries[$index - 1] : null;
             $hike = 0;
             if ($prev && $prev->annual_ctc > 0) {
                 $hike = (($salary->annual_ctc - $prev->annual_ctc) / $prev->annual_ctc) * 100;
             }
             
             return [
                 'year' => Carbon::parse($salary->effective_date)->year,
                 'date' => Carbon::parse($salary->effective_date)->format('M Y'),
                 'ctc' => $salary->annual_ctc,
                 'hike_percent' => round($hike, 1),
                 'designation' => $salary->structure->name ?? 'N/A', // Or pull from Employee? Structure name is often level.
                 'rating' => 0 // Placeholder for Performance Link
             ];
        });

        // Calculate CAGR if > 1 year
        $cagr = 0;
        if ($salaries->count() > 1) {
            $first = $salaries->first();
            $last = $salaries->last();
            $years = Carbon::parse($first->effective_date)->floatDiffInYears(Carbon::parse($last->effective_date));
            if ($years >= 1 && $first->annual_ctc > 0) {
                $cagr = (pow($last->annual_ctc / $first->annual_ctc, 1 / $years) - 1) * 100;
            } else {
                 // Simple growth for <1 year
                 if ($first->annual_ctc > 0)
                    $cagr = (($last->annual_ctc - $first->annual_ctc) / $first->annual_ctc) * 100;
            }
        }

        return [
            'timeline' => $timeline,
            'cagr' => round($cagr, 1),
            'latest_ctc' => $salaries->last()?->annual_ctc ?? 0
        ];
    }

    /**
     * Get Data for Bell Curve Visualization.
     * Groups employees by Performance Rating (1-5).
     */
    public function getBellCurve($departmentId = null)
    {
        // Assuming 'appraisals' or 'performance_reviews' table
        // For now, we might not have the table, so we return Mock Structure 
        // that the Controller can populate or we use a raw query if table exists.
        
        // Let's assume we use 'employees.performance_rating' if it existed, or related model.
        // I will return a placeholder structure that ensures Frontend doesn't break 
        // while we wait for the Performance Logic to be fully tied in.
        
        return [
            '1' => 5,   // Needs Improvement
            '2' => 10,  // Below Average
            '3' => 60,  // Meets Expectations (Target 60%)
            '4' => 20,  // Exceeds
            '5' => 5    // Outstanding
        ];
    }

    /**
     * Get Data for Scatter Plot (Pay Hike vs Performance).
     */
    public function getPayVsPerformance($departmentId = null)
    {
        // Mocking the Scatter Data for visualization proof-of-concept
        // In real impl, we join `appraisals` with `salary_hikes`.
        
        $data = [];
        for ($i=0; $i<50; $i++) {
            $rating = rand(10, 50) / 10; // 1.0 to 5.0
            $hike = ($rating * 3) + rand(-2, 5); // Correlation with noise
            if ($hike < 0) $hike = 0;
            
            // Anomalies (Red Dots)
            if ($i % 20 == 0) { 
                $rating = 5.0; 
                $hike = 2.0; // Underpaid Star
            }
            if ($i % 25 == 0) {
                $rating = 2.0;
                $hike = 15.0; // Overpaid Low Performer
            }

            $data[] = [
                'x' => $rating, // Rating
                'y' => $hike,   // Hike %
                'name' => "Emp $i",
                'id' => $i
            ];
        }
        return $data;
    }
    
    /**
     * Get Budget Consumption by Department.
     */
    public function getBudgetHeatmap()
    {
        // Aggregate Current CTC by Dept
         return Employee::join('employee_salaries', 'employees.id', '=', 'employee_salaries.employee_id')
            ->where('employee_salaries.is_active', true)
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->selectRaw('departments.name, SUM(employee_salaries.annual_ctc) as total_budget')
            ->groupBy('departments.name')
            ->get()
            ->map(function($item) {
                return [
                    'x' => $item->name,
                    'y' => $item->total_budget
                ];
            });
    }
}
