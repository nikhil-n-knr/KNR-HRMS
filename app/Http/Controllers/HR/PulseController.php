<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PulseController extends Controller
{
    public function index(Request $request)
    {
        // For now, listing all employees with their latest metrics
        // In a real app, this would be scoped to "My Teams" or filtered
        
        $metrics = DB::table('performance_metrics')
            ->join('employees', 'performance_metrics.employee_id', '=', 'employees.id')
            ->select(
                'employees.first_name', 
                'employees.last_name',
                'performance_metrics.*'
            )
            ->where('month', now()->format('Y-m')) // Current month default
            ->orderByDesc('quality_score')
            ->get();

        return Inertia::render('HR/Performance/PulseDashboard', [
            'metrics' => $metrics,
            'currentMonth' => now()->format('F Y')
        ]);
    }
}
