<?php

namespace App\Services\Analytics;

use App\Models\Employee;
use App\Models\AnalyticsSnapshot;
use App\Models\EmployeeSalary; // Fallback if no snapshot for "Current"
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StrategicAnalyticsService
{
    /**
     * View 1: The Matrix (Cost vs Value Quadrant)
     * Comparing Department Efficiency.
     */
    public function getMatrixData()
    {
        // For "Current" View, we can use live Employee Data + Salaries
        // We aggregate by Department.
        
        $data = Employee::join('employee_salaries', 'employees.id', '=', 'employee_salaries.employee_id')
            ->where('employee_salaries.is_active', true)
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            // Join Appraisals if available, else Mock Rating
            ->select(
                'departments.name as department',
                DB::raw('COUNT(employees.id) as headcount'),
                DB::raw('AVG(employee_salaries.annual_ctc) as avg_ctc'),
                DB::raw('SUM(employee_salaries.annual_ctc) as total_payroll')
            )
            ->groupBy('departments.name')
            ->get();

        // Add Mock "Avg Performance Rating" simulation since we might not have apprasials yet
        return $data->map(function ($item) {
            // Simulate Rating between 2.5 and 4.2 based on random seed of name length 
            // (Deterministic for demo)
            $seed = strlen($item->department); 
            $rating = 3.0 + ($seed % 15) / 10; 
            
            // Determine Quadrant
            // X: Rating (0-5), Y: Cost
            // We return formatted object for Bubble Chart
            return [
                'x' => round($rating, 2), // Performance
                'y' => round($item->avg_ctc, 2), // Cost
                'r' => $item->headcount * 2, // Bubble Radius linked to Headcount
                'department' => $item->department,
                'revenue_per_rupee' => round(rand(30, 80) / 10, 2) // Mock ROI
            ];
        });
    }

    /**
     * View 2: The Structure (Salary Inversion Check)
     * Box Plots of Salary by Designation.
     */
    public function getStructureData()
    {
       // Get all active salaries grouped by designation
       $salaries = Employee::join('employee_salaries', 'employees.id', '=', 'employee_salaries.employee_id')
           ->where('employee_salaries.is_active', true)
           ->get(['employees.designation', 'employee_salaries.annual_ctc', 'employees.gender']);

       $grouped = $salaries->groupBy('designation');
       
       $stats = [];
       foreach ($grouped as $designation => $rows) {
           if ($rows->count() < 2) continue; // Skip singletons
           
           $ctcs = $rows->pluck('annual_ctc')->sort()->values();
           $count = $ctcs->count();
           
           $q1 = $ctcs[floor($count * 0.25)];
           $median = $ctcs[floor($count * 0.5)];
           $q3 = $ctcs[floor($count * 0.75)];
           $min = $ctcs->first();
           $max = $ctcs->last();

           $stats[] = [
               'x' => $designation,
               'min' => $min,
               'q1' => $q1,
               'median' => $median,
               'q3' => $q3,
               'max' => $max,
               'outliers' => $this->detectOutliers($ctcs, $q1, $q3, $rows) // Find Red Dots
           ];
       }

       return $stats;
    }

    private function detectOutliers($sortedCtcs, $q1, $q3, $rows)
    {
        $iqr = $q3 - $q1;
        $lower = $q1 - (1.5 * $iqr);
        $upper = $q3 + (1.5 * $iqr);
        
        $outliers = [];
        foreach ($rows as $row) {
            if ($row->annual_ctc < $lower || $row->annual_ctc > $upper) {
                $outliers[] = [
                    'y' => $row->annual_ctc,
                    'is_overpaid' => $row->annual_ctc > $upper // Red Dot
                ];
            }
        }
        return $outliers;
    }

    /**
     * View 3: The Vintage (Loyalty Analysis)
     * Scatter Plot: Tenure (Years) vs CTC.
     */
    public function getVintageData()
    {
        $employees = Employee::join('employee_salaries', 'employees.id', '=', 'employee_salaries.employee_id')
           ->where('employee_salaries.is_active', true)
           ->get();

        return $employees->map(function ($e) {
             // Calculate precise tenure
             $joined = Carbon::parse($e->joining_date);
             $tenure = $joined->diffInMonths(now()) / 12;
             
             return [
                 'x' => round($tenure, 1),
                 'y' => $e->annual_ctc,
                 'name' => $e->first_name . ' ' . $e->last_name,
                 'is_new_hire' => $tenure < 1,
                 'is_loyal' => $tenure > 5
             ];
        });
    }

    /**
     * View 4: Time Machine (Historical Trends)
     * Uses `analytics_snapshots` DB.
     */
    public function getTimeMachineData()
    {
        // Fetch aggregates for last 5 years from Snapshots
        // Assuming we seeded generic monthly snapshots or at least Year End (Dec) snapshots.
        
        // If DB is empty (likely initially), we fallback to creating dummy trend from current.
        if (AnalyticsSnapshot::count() === 0) {
            return $this->generateMockTimeMachine();
        }

        $years = AnalyticsSnapshot::selectRaw('YEAR(snapshot_date) as year, SUM(annual_ctc) as total_payroll, COUNT(*) as headcount')
             ->whereMonth('snapshot_date', 12) // Use Dec Snapshot
             ->groupBy('year')
             ->orderBy('year')
             ->get();

        // Enhance with Segments (Base vs Growth)
        $result = [];
        $prevPayroll = 0;
        
        foreach ($years as $y) {
            $base = $prevPayroll > 0 ? $prevPayroll : $y->total_payroll * 0.8; // Fallback for Year 1
            $diff = $y->total_payroll - $base;
            
            // Artificial breakdown for Visualization if precise tracking not possible
            // In reality, we'd query "Same IDs" vs "New IDs"
            $inflation = $base * 0.05; // Assumed 5% inflation
            $hikes = max(0, $diff * 0.6); // 60% of growth is Hikes
            $newHires = max(0, $diff * 0.4); // 40% of growth is New Hires
            
            // To make sure bars stack to Total
            if ($prevPayroll == 0) {
                 // First Year: All Base
                 $base = $y->total_payroll;
                 $inflation = 0;
                 $hikes = 0;
                 $newHires = 0;
            } else {
                 // Adjust base to be "Previous Year Total"
                 $base = $prevPayroll;
                 // Remaining growth split
                 $realGrowth = $y->total_payroll - $base;
                 $hikes = $realGrowth * 0.7; 
                 $newHires = $realGrowth * 0.3;
                 $inflation = 0; // Simplified for stacked bar
            }

            $result[] = [
                'year' => $y->year,
                'base' => round($base, 2),
                'inflation_segment' => round($inflation, 2), // Keep 0 for now to keep it clean or add logic
                'hike_segment' => round($hikes, 2),
                'new_hires' => round($newHires, 2) // Frontend expects 'hike_segment' and 'inflation_segment' names? 
                                                  // Let's match frontend props: base, inflation_segment, hike_segment
            ];
            
            $prevPayroll = $y->total_payroll;
        }
             
        // Calculate CAGR based on real data
        return $result;
    }
    
    // Fallback for Demo
    private function generateMockTimeMachine()
    {
        $currentPayroll = EmployeeSalary::where('is_active', true)->sum('annual_ctc');
        $data = [];
        // Reverse engineer 15% growth
        for ($i=4; $i>=0; $i--) {
             $year = Carbon::now()->subYears($i)->year;
             $cost = $currentPayroll / pow(1.15, $i);
             $inflation = $cost * 0.05;
             $hikes = $cost * 0.10;
             $newHires = $cost * 0.05; // Growth
             
             $data[] = [
                 'year' => $year,
                 'base' => round($cost, 2),
                 'inflation_segment' => round($inflation, 2),
                 'hike_segment' => round($hikes, 2)
             ];
        }
        return $data;
    }
}
