<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Services\HR\Employee360Service;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class Employee360Controller extends Controller
{
    protected $service;

    public function __construct(Employee360Service $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {

        // 1. RBAC Check (Super Admin, Admin, HR Manager, Manager)
        if (!Auth::user()->hasRole(['Super Admin', 'Admin', 'HR Manager', 'Manager'])) {
            abort(403, 'Unauthorized access to 360 Employee Reports.');
        }

        // 2. Fetch accessible employees based on role
        $query = Employee::with('user:id,name,email')->where('status', 'active');
        
        if (Auth::user()->hasRole('Manager') && !Auth::user()->hasRole(['Super Admin', 'Admin', 'HR Manager'])) {
            // Managers only see themselves or their direct reports (assuming reporting_to logic or teams)
            $query->where('reporting_to', Auth::id());
        }

        $employees = $query->get()->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->user->name ?? $e->first_name . ' ' . $e->last_name,
                'designation' => $e->designation
            ];
        });

        return Inertia::render('HR/Employee360/Dashboard', [
            'employees' => $employees,
            'default_date_start' => now()->subDays(30)->format('Y-m-d'),
            'default_date_end' => now()->format('Y-m-d')
        ]);
    }

    public function getMetrics(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $start = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->input('end_date', now()->format('Y-m-d'));

        $metrics = $this->service->getDashboardMetrics($employee, $start, $end);

        // Load profile extra details
        $profile = [
            'name' => $employee->user->name ?? ($employee->first_name . ' ' . $employee->last_name),
            'avatar' => $employee->avatar,
            'designation' => $employee->designation,
            'department' => $employee->department->name ?? 'N/A'
        ];

        return response()->json([
            'profile' => $profile,
            'metrics' => $metrics
        ]);
    }

    public function export(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $start = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->input('end_date', now()->format('Y-m-d'));

        return Excel::download(
            new \App\Exports\Employee360Export($employee, $start, $end, clone $this->service),
            'Employee_360_Report_' . str_replace(' ', '_', $employee->first_name) . '_' . date('Y-m-d') . '.xlsx'
        );
    }

    protected function authorizeAccess(Employee $employee)
    {
        if (!Auth::user()->hasRole(['Super Admin', 'Admin', 'HR Manager', 'Manager'])) {
            abort(403);
        }

        if (Auth::user()->hasRole('Manager') && !Auth::user()->hasRole(['Super Admin', 'Admin', 'HR Manager'])) {
            if ($employee->reporting_to !== Auth::id()) {
                abort(403, 'You can only view your direct reports.');
            }
        }
    }
}
