<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use App\Services\Infrastructure\LoggerService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class EmployeeFamilyController extends Controller
{
    use ApiResponser;

    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index($employeeId)
    {
        $families = EmployeeFamily::where('employee_id', $employeeId)->get();
        return $this->success($families);
    }

    public function store(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|in:spouse,child,father,mother,sibling,other',
            'dob' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_dependent' => 'boolean',
            'is_emergency_contact' => 'boolean'
        ]);

        $family = $employee->families()->create($validated);

        $this->logger->log('employee_management', 'update', "Added family member for {$employee->first_name}: {$family->name}");

        return $this->success($family, 'Family member added successfully', 201);
    }

    public function update(Request $request, $employeeId, $id)
    {
        $family = EmployeeFamily::where('employee_id', $employeeId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|in:spouse,child,father,mother,sibling,other',
            'dob' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_dependent' => 'boolean',
            'is_emergency_contact' => 'boolean'
        ]);

        $family->update($validated);
        
        $this->logger->log('employee_management', 'update', "Updated family member: {$family->name}");

        return $this->success($family, 'Family member updated');
    }

    public function destroy($employeeId, $id)
    {
        $family = EmployeeFamily::where('employee_id', $employeeId)->findOrFail($id);
        $family->delete();
        
        $this->logger->log('employee_management', 'update', "Deleted family member: {$family->name}");

        return $this->success(null, 'Family member deleted');
    }
}
