<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Loan;
use DB;

class AnalyticsController extends Controller
{
    public function getPayrollStats()
    {
        // 1. Payout Trend (Last 6 Months)
        $trend = Payroll::where('status', 'Paid')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->take(6) // Should be latest 6 but ordered
            ->get();
            
        // Fix ordering logic: take latest 6, then sort chronological
        $trend = Payroll::where('status', 'Paid')
            ->latest()
            ->take(6)
            ->get()
            ->sortBy(function($p) {
                return sprintf('%d-%02d', $p->year, $p->month);
            });
            
        $labels = $trend->map(fn($p) => $p->start_date->format('M Y'))->values();
        $data = $trend->map(fn($p) => $p->total_payout)->values();
        
        // 2. Headcount Cost Distribution (Dept wise)
        // Hard to do exact historical efficiently without snapshots. 
        // We'll return current distribution for now.
        $deptDist = \App\Models\EmployeeSalary::with('employee.department')
            ->get()
            ->groupBy('employee.department.name')
            ->map(fn($group) => $group->sum('monthly_ctc')); // Approx
            
        return response()->json([
            'trend' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Total Payout',
                    'data' => $data,
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'tension' => 0.4
                ]]
            ],
            'distribution' => [
                 'labels' => $deptDist->keys(),
                 'datasets' => [[
                     'data' => $deptDist->values(),
                     'backgroundColor' => ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#6366F1']
                 ]]
            ]
        ]);
    }

    public function getLoanStats()
    {
        $totalDisbursed = Loan::where('status', '!=', 'Rejected')->sum('principal_amount');
        
        $totalRecovered = \App\Models\LoanRepayment::where('status', 'Deducted')->sum('amount');
        
        $activeLoans = Loan::active()->count();
        
        // Recovered vs Pending by Month (Last 6 months)
        // This is complex, skipping graph for V1 of Loans stats. Just Cards.
        
        return response()->json([
            'cards' => [
                ['title' => 'Total Disbursed', 'value' => '₹ ' . number_format($totalDisbursed)],
                ['title' => 'Recovered', 'value' => '₹ ' . number_format($totalRecovered)],
                ['title' => 'Active Loans', 'value' => $activeLoans],
            ]
        ]);
    }
}
