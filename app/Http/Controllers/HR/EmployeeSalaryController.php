<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\SalaryStructure;
use App\Services\Payroll\SalaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeSalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Show Salary Management for an Employee.
     */
    public function index(Employee $employee)
    {
        $employee->load('user');
        
        $currentSalary = EmployeeSalary::where('employee_id', $employee->id)
                            ->where('is_active', true)
                            ->with('structure.components')
                            ->first();
                            
        $history = EmployeeSalary::where('employee_id', $employee->id)
                        ->where('is_active', false)
                        ->with('structure')
                        ->orderByDesc('effective_date')
                        ->get();

        $structures = SalaryStructure::where('is_active', true)->get();

        return Inertia::render('HR/Employees/Salary', [
            'employee' => $employee,
            'currentSalary' => $currentSalary,
            'history' => $history,
            'structures' => $structures
        ]);
    }

    /**
     * Assign New Salary (Appraisal / Conversion).
     */
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'salary_structure_id' => 'required|exists:salary_structures,id',
            'annual_ctc' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'remarks' => 'nullable|string'
        ]);

        try {
            $effectiveDate = $request->effective_date;
            // Basic validation: Effective date should essentially be future or current mostly, 
            // but back-dated appraisals are real. So no strict check.

            $this->salaryService->assignSalary(
                $employee, 
                $request->salary_structure_id, 
                $request->annual_ctc, 
                $effectiveDate,
                $request->remarks
            );

            return back()->with('success', 'Salary Updated Successfully')->setStatusCode(303);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }
}
