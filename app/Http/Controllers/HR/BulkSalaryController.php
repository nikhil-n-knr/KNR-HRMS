<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Services\Payroll\SalaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BulkSalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    public function index(Request $request)
    {
        // 1. Fetch Employees with their CURRENT Active Salary
        $query = Employee::with(['department', 'user', 'latestSalary.structure'])
            ->where('status', 'active'); // Only active employees

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->get()
            ->filter(fn($emp) => $emp->user) // Defensive: Only show employees with users
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->user->name,
                    'department' => $emp->department->name ?? '-',
                    'current_structure_id' => $emp->latestSalary?->salary_structure_id,
                    'current_structure_name' => $emp->latestSalary?->structure?->name,
                    'current_ctc' => $emp->latestSalary?->annual_ctc ?? 0,
                    // Fields for editing
                    'new_structure_id' => $emp->latestSalary?->salary_structure_id, 
                    'new_ctc' => $emp->latestSalary?->annual_ctc ?? 0,
                    'effective_date' => now()->format('Y-m-d'),
                    'selected' => false
                ];
            })->values();

        $structures = SalaryStructure::where('is_active', true)->select('id', 'name')->get();
        $departments = \App\Models\Department::select('id', 'name')->get();

        return Inertia::render('HR/Payroll/Bulk/Index', [
            'employees' => $employees,
            'structures' => $structures,
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:employees,id',
            'updates.*.new_structure_id' => 'required|exists:salary_structures,id',
            'updates.*.new_ctc' => 'required|numeric|min:0',
            'updates.*.effective_date' => 'required|date'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->updates as $update) {
                $employee = Employee::find($update['id']);
                
                // Only update if changed (Optimization)
                $currentSalary = $employee->latestSalary;
                // Optimization Removed: User might want to re-apply the same structure 
                // (e.g. if underlying components changed) or just update the effective date.
                // We proceed with assignment regardless of equality.


                $this->salaryService->assignSalary(
                    $employee,
                    $update['new_structure_id'],
                    $update['new_ctc'],
                    $update['effective_date'],
                    'Bulk Update'
                );
            }
        });

        return back()->with('success', 'Bulk salaries updated successfully.')
            ->setStatusCode(303);
    }

    /**
     * Download a sample CSV sheet pre-filled with all active employees.
     * Users fill in the new_ctc / effective_date and re-import.
     */
    public function sampleSheet()
    {
        $employees = Employee::with(['department', 'user', 'latestSalary.structure'])
            ->where('status', 'active')
            ->get()
            ->filter(fn($e) => $e->user);

        $headers = [
            'employee_id',
            'employee_name',
            'department',
            'current_structure',
            'current_ctc',
            'new_structure_id',    // Fill this in (ID from salary_structures table)
            'new_ctc',             // Fill this in (annual CTC in INR)
            'effective_date',      // Fill this in (YYYY-MM-DD format)
        ];

        return response()->streamDownload(function () use ($employees, $headers) {
            $handle = fopen('php://output', 'w');

            // Instruction row
            fputcsv($handle, [
                '# INSTRUCTIONS: Fill in new_structure_id, new_ctc, and effective_date.',
                'Leave new_structure_id blank to keep current structure.',
                'effective_date format: YYYY-MM-DD',
                '', '', '', '', ''
            ]);

            fputcsv($handle, $headers);

            foreach ($employees as $emp) {
                fputcsv($handle, [
                    $emp->id,
                    $emp->user->name,
                    $emp->department->name ?? '-',
                    $emp->latestSalary?->structure?->name ?? '-',
                    $emp->latestSalary?->annual_ctc ?? 0,
                    $emp->latestSalary?->salary_structure_id ?? '',
                    $emp->latestSalary?->annual_ctc ?? 0,
                    now()->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, 'bulk_salary_template_' . now()->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
