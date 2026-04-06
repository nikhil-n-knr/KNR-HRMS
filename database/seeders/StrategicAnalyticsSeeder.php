<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\AnalyticsSnapshot;
use App\Models\Department;
use App\Models\EmployeeSalary;
use App\Models\SalaryStructure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Payroll\SalaryService;

class StrategicAnalyticsSeeder extends Seeder
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Run the database seeds.
     * Generates 3 Years of History for a rich analytics experience.
     */
    public function run()
    {
        // 1. Ensure we have Departments
        $depts = ['Engineering', 'Sales', 'Product', 'HR', 'Marketing', 'Finance'];
        $deptIds = [];
        $tenant = \App\Models\Tenant::first();
        foreach ($depts as $d) {
             $dept = Department::firstOrCreate(
                ['name' => $d, 'tenant_id' => $tenant->id], 
                ['status' => 'active']
             );
             $deptIds[$d] = $dept->id;
        }

        $employees = Employee::all();
        
        $designations = [
            'Engineering' => ['Intern', 'Software Engineer', 'Senior Engineer', 'Tech Lead', 'Staff Engineer', 'Engineering Manager'],
            'Sales' => ['BDR', 'Account Executive', 'Sales Manager', 'VP Sales'],
            'HR' => ['HR Executive', 'Talent Acquisition', 'HRBP', 'Head of People'],
            'Product' => ['APM', 'Product Manager', 'Senior PM', 'Group PM'],
            'Marketing' => ['Content Writer', 'Marketing Manager', 'CMO'],
            'Finance' => ['Accountant', 'Finance Controller', 'CFO']
        ];
        
        $baseSalaries = [
            'Intern' => 300000,
            'Software Engineer' => 800000,
            'Senior Engineer' => 1500000,
            'Tech Lead' => 2400000,
            'Staff Engineer' => 3500000,
            'Engineering Manager' => 4000000,
            'BDR' => 500000,
            'Account Executive' => 1200000,
            'Sales Manager' => 2500000,
            'HR Executive' => 450000,
            'HRBP' => 1200000,
            'Product Manager' => 1800000
        ];

        // Clear old snapshots
        AnalyticsSnapshot::truncate();

        $structure = SalaryStructure::first();

        foreach ($employees as $index => $emp) {
            $deptName = $depts[$index % count($depts)];
            $roles = $designations[$deptName] ?? $designations['Engineering'];
            
            // Assign Metadata
            $currentRole = $roles[rand(1, count($roles) - 1)]; 
            $gender = $index % 3 == 0 ? 'Female' : 'Male';
            $tenureYears = rand(1, 8);
            $joinDate = Carbon::now()->subYears($tenureYears)->subDays(rand(0, 300));
            
            $emp->update([
                'department_id' => $deptIds[$deptName],
                'designation' => $currentRole,
                'joining_date' => $joinDate,
                'gender' => $gender,
                'status' => 'active'
            ]);

            $base = $baseSalaries[$currentRole] ?? 1000000;
            $currentCtc = $base * (1 + (rand(-10, 20) / 100)); 
            
            // Calculate Breakdown
            $breakdown = $this->salaryService->calculateBreakdown($currentCtc, $structure->id);
            
            EmployeeSalary::updateOrCreate(
                ['employee_id' => $emp->id],
                [
                    'annual_ctc' => $currentCtc,
                    'is_active' => true,
                    'effective_date' => Carbon::now()->startOfYear(),
                    'salary_structure_id' => $structure->id,
                    'breakdown' => $breakdown
                ]
            );

            // History Generation
            $historyCtc = $currentCtc;
            $historyRole = $currentRole;
            
            for ($m = 0; $m <= 36; $m++) {
                $date = Carbon::now()->startOfMonth()->subMonths($m);
                if ($date->lt($joinDate)) break;

                if ($date->month == 3 && $m > 0) { 
                     $historyCtc = $historyCtc / (1.10 + (rand(0, 10)/100));
                }

                if ($m == 24 && $date->month == 3) {
                     $roleIndex = array_search($historyRole, $roles);
                     if ($roleIndex > 0) {
                          $historyRole = $roles[$roleIndex - 1];
                          $historyCtc = $historyCtc * 0.7;
                     }
                }

                $rating = 3 + (rand(0, 20) / 10);
                if ($historyCtc > 2000000) $rating += 0.5;

                AnalyticsSnapshot::create([
                    'snapshot_date' => $date->format('Y-m-d'),
                    'employee_id' => $emp->id,
                    'department_name' => $deptName,
                    'designation' => $historyRole,
                    'annual_ctc' => round($historyCtc, 2),
                    'performance_rating' => min(5, round($rating, 1)),
                    'tenure_months' => $joinDate->diffInMonths($date),
                    'gender' => $gender,
                    'location_name' => $index % 2 == 0 ? 'Bangalore' : 'Mumbai'
                ]);
            }
        }
    }
}
