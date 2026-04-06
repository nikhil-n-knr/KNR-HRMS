<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TaxDeclaration;
use App\Models\EmployeeHraDeclaration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TaxReportController extends Controller
{
    /**
     * The Analytics Dashboard
     */
    public function dashboard()
    {
        // 1. Completion Rate
        $totalEmployees = Employee::where('status', 'active')->count(); // Or those who submitted declarations
        $employeesWithProofs = TaxDeclaration::where('status', '!=', 'Pending')
            ->distinct('employee_id')->count('employee_id');
        
        $completionRate = $totalEmployees > 0 ? round(($employeesWithProofs / $totalEmployees) * 100) : 0;

        // 2. Status Counts (Declarations Level, not Employee Level for granularity)
        $stats = TaxDeclaration::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $verified = $stats['Verified'] ?? 0;
        $rejected = $stats['Rejected'] ?? 0;
        $pending = $stats['Submitted'] ?? 0;
        $total = $verified + $rejected + $pending;
        
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;

        // 3. Tax Impact (Approximation)
        // Sum of Verified Amount * 0.3 (Assuming top bracket for impact visualization)
        // In reality, use TaxCalculator Service, but for dashboard speed use sum.
        $totalVerifiedAmount = TaxDeclaration::where('status', 'Verified')->sum('verified_amount');
        $taxSaved = $totalVerifiedAmount * 0.312; // 30% + Cess

        // 4. Pending High Value (> 1L)
        $highValuePending = TaxDeclaration::where('status', 'Submitted')
            ->where('declared_amount', '>', 100000)
            ->count();
            
        // 5. Velocity (Last 7 Days)
        $velocity = TaxDeclaration::where('status', 'Verified')
            ->where('updated_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get();

        return Inertia::render('HR/Tax/Analytics/Index', [
            'stats' => [
                'completion_rate' => $completionRate,
                'verified_count' => $verified,
                'rejected_count' => $rejected,
                'rejection_rate' => $rejectionRate,
                'pending_count' => $pending,
                'tax_saved_approx' => $taxSaved,
                'high_value_pending' => $highValuePending
            ],
            'velocity' => $velocity
        ]);
    }
    
    /**
     * Reports Export Hub
     */
    public function index() {
        return Inertia::render('HR/Tax/Reports/Index');
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'master');
        
        if ($type === 'defaulters') {
            return $this->exportDefaulters();
        }
        
        // Default: Master Report
        return $this->exportMaster();
    }
    
    // Private Export Methods
    private function exportMaster() {
        $data = TaxDeclaration::with('employee', 'section')->get();
        // Generate CSV string
        $csv = "Employee, Code, Section, Claimed, Verified, Status, Remarks\n";
        foreach($data as $row) {
            $csv .= "{$row->employee->name}, {$row->employee->employee_code}, {$row->section->name}, {$row->declared_amount}, {$row->verified_amount}, {$row->status}, {$row->remarks}\n";
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="verification_master.csv"');
    }
    
    private function exportDefaulters() {
        // Find Employees with Declarations > 0 but NO Uploaded Proofs 
        // Logic: Has Declaration with amount > 0, but proofs_count = 0
        // Or status is 'Pending' and not 'Submitted' (if Submit moves to Submitted)
        
        // Simplifying logic: Status is 'Pending' (Declaration made, but not "Submitted" with proofs)
        $defaulters = TaxDeclaration::with('employee')
            ->where('status', 'Pending') 
            ->where('declared_amount', '>', 0)
            ->get();
            
        $csv = "Employee, Code, Section, Planned Amount, Status\n";
        foreach($defaulters as $row) {
             $csv .= "{$row->employee->name}, {$row->employee->employee_code}, {$row->section->name} ({$row->section->section_code}), {$row->declared_amount}, Not Submitted\n";
        }
        
        return response($csv)
             ->header('Content-Type', 'text/csv')
             ->header('Content-Disposition', 'attachment; filename="defaulters_list.csv"');
    }
}
