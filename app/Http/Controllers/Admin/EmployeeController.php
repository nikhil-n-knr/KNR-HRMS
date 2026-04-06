<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\Infrastructure\LoggerService;
use App\Services\Infrastructure\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    protected $logger;
    protected $activityLogger;
    protected $kitService;
    protected $clearanceService;

    public function __construct(
        LoggerService $logger,
        ActivityLogger $activityLogger,
        \App\Services\Assets\KitService $kitService,
        \App\Services\Exit\ClearanceService $clearanceService
    )
    {
        $this->logger = $logger;
        $this->activityLogger = $activityLogger;
        $this->kitService = $kitService;
        $this->clearanceService = $clearanceService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('employee_management.employees.view_list') && 
            !auth()->user()->hasPermission('employee_management.employees.view')) {
             abort(403);
        }

        $query = Employee::with(['department', 'location', 'user']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $employees = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Employee/EmployeeList', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'department_id', 'status']),
            'departments' => \App\Models\Department::select('id', 'name')->get() // For filter dropdown
        ]);
    }
    
    public function export(Request $request)
    {
        if (!auth()->user()->hasPermission('employee_management.employees.view_list') && 
            !auth()->user()->hasPermission('employee_management.employees.view')) {
             abort(403);
        }

        $query = Employee::with(['department', 'location']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->streamDownload(function() use ($query) {
             $handle = fopen('php://output', 'w');
             fputcsv($handle, ['Code', 'Name', 'Email', 'Department', 'Location', 'Designation', 'Status', 'Joining Date']);
             
             $query->chunk(200, function($rows) use ($handle) {
                 foreach($rows as $row) {
                     fputcsv($handle, [
                         $row->employee_code,
                         $row->first_name . ' ' . $row->last_name,
                         $row->email,
                         $row->department->name ?? '-',
                         $row->location->name ?? '-',
                         $row->designation,
                         ucfirst($row->status),
                         $row->joining_date
                     ]);
                 }
             });
             fclose($handle);
        }, 'employees_' . date('Y-m-d') . '.csv');
    }

    public function create()
    {
        return Inertia::render('Employee/EmployeeCreate', [
            'departments' => \App\Models\Department::select('id', 'name')->get(),
            'locations' => \App\Models\Location::select('id', 'name')->get(),
            'roles' => \App\Models\Role::select('id', 'name')->get(), // For user creation if integrated
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'location_id' => 'required|exists:locations,id',
            'designation' => 'required|string|max:100',
            'joining_date' => 'required|date',
            'status' => 'required|in:active,probation,notice_period,terminated,resigned,on_leave',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'employee_code' => 'required|unique:employees,employee_code',
        ]);

        $employee = Employee::create($validated);
        
        $this->logger->log('employee_management', 'create', "Employee created: {$employee->employee_code}");

        return redirect()->route('admin.employees.show', $employee->id)->with('success', 'Employee created successfully');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'department', 
            'location', 
            'user.roles', 
            'manager',
            'personalDetail',
            'healthRecord',
            'families',
            'bankDetails',
            'latestSalary.structure'
        ]);

        // Fetch audit history for this employee from the activity log
        $history = \DB::table('activity_logs')
            ->where('subject_id', $employee->id)
            ->where('subject_type', 'like', '%Employee%')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id'          => $log->id,
                    'description' => $log->description,
                    'properties'  => $log->properties ? json_decode($log->properties, true) : null,
                    'created_at'  => $log->created_at,
                ];
            });

        return Inertia::render('Employee/EmployeeProfile', [
            'employee' => $employee,
            'history'  => $history,
            'tab'      => request()->query('tab', 'personal'),
        ]);
    }

    public function edit(Employee $employee)
    {
        // Typically in this SPA, Edit is handled within the Profile page or a specific Edit form.
        // If we want a dedicated Edit page, we should have EmployeeEdit.vue.
        // Given we saw EmployeeForm.vue (maybe shared component?), let's assume users might want to Edit 
        // the core details (Index -> Edit).
        // For now, let's redirect to Show/Profile where editing happens, OR replicate Create logic if Form is reusable.
        // Let's check if EmployeeCreate uses EmployeeForm. 
        // Providing a basic scaffolding for Edit to avoid 404/500 if someone hits the route.
        // Better: Redirect to Show page which likely has "Edit" tabs.
        
        return redirect()->route('admin.employees.show', $employee->id);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'sometimes|exists:departments,id',
            'location_id' => 'sometimes|exists:locations,id',
            'designation' => 'sometimes|string|max:100',
            'joining_date' => 'sometimes|date',
            'status' => 'sometimes|in:active,probation,notice_period,terminated,resigned,on_leave',
            'employment_type' => 'sometimes|in:full_time,part_time,contract,intern',
            'reporting_to' => 'nullable|exists:users,id',
            'is_international_worker' => 'boolean',
            'is_director' => 'boolean'
        ]);

        return DB::transaction(function () use ($request, $employee) {
            $oldStatus = $employee->status;
            $employee->update($request->all());
            
            // Trigger Exit Clearance if status changed to Resigned/Terminated
            if (in_array($request->status, ['resigned', 'terminated']) && !in_array($oldStatus, ['resigned', 'terminated'])) {
                 $this->clearanceService->initiateClearance($employee);
            }

            $this->logger->log('employee_management', 'update', "Employee updated: {$employee->employee_code}");
            return redirect()->back()->with('success', 'Employee updated successfully');
        });
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        $this->logger->log('employee_management', 'delete', "Employee deleted: {$employee->employee_code}");
        return response()->noContent();
    }

    /**
     * Create User Login for Employee.
     */
    public function createLogin(Request $request, Employee $employee)
    {
        if ($employee->user_id) {
            return response()->json(['message' => 'Employee already has login access'], 422);
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|exists:roles,name'
        ]);

        return DB::transaction(function () use ($validated, $employee) {
            $user = \App\Models\User::create([
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'tenant_id' => $employee->tenant_id,
                'department_id' => $employee->department_id,
                'location_id' => $employee->location_id,
                'status' => 'active',
            ]);

            $user->assignRole($validated['role']);

            // Link to Employee
            $employee->update(['user_id' => $user->id]);

            // Auto-assign Asset Kit
            try {
                $kitResult = $this->kitService->autoAssignByDesignation($employee, auth()->id());
                if ($kitResult) {
                    $this->logger->log('asset_management', 'auto_assign', $kitResult);
                }
            } catch (\Exception $e) {
                 $this->logger->log('asset_management', 'error', "Auto-assign failed: " . $e->getMessage());
            }

            $this->logger->log('employee_management', 'update', "Created login for employee: {$employee->employee_code}");

            // Log to activity trail
            $this->activityLogger->log(
                "System login access created (Email: {$validated['email']})",
                $employee,
                ['role' => $validated['role'], 'email' => $validated['email']]
            );

            return response()->json($user, 201);
        });
    }

    /**
     * Toggle Employee Status.
     */
    public function toggleStatus(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,probation,notice_period,terminated,resigned,on_leave'
        ]);

        $oldStatus = $employee->status;
        $employee->update(['status' => $validated['status']]);
        
        // Sync User Status if linked
        if ($employee->user_id) {
            $userStatus = in_array($validated['status'], ['terminated', 'resigned']) ? 'inactive' : 'active';
            $employee->user->update(['status' => $userStatus]);
        }
        
        // Trigger Exit Clearance
        if (in_array($validated['status'], ['resigned', 'terminated']) && !in_array($oldStatus, ['resigned', 'terminated'])) {
             $this->clearanceService->initiateClearance($employee);
        }

        // Log to activity trail
        $this->activityLogger->log(
            "Status changed from '{$oldStatus}' to '{$validated['status']}'",
            $employee,
            ['old_status' => $oldStatus, 'new_status' => $validated['status']]
        );

        return response()->json($employee);
    }

    /**
     * Reset Employee User Password.
     */
    public function resetPassword(Request $request, Employee $employee)
    {
        if (!$employee->user_id) {
            return response()->json(['message' => 'Employee has no login account'], 422);
        }

        $validated = $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);

        $employee->user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password'])
        ]);

        $this->logger->log('employee_management', 'security', "Password reset for employee: {$employee->employee_code}");

        // Log to activity trail
        $this->activityLogger->log(
            'Password was reset by admin',
            $employee
        );

        return response()->json(['message' => 'Password reset successfully']);
    }

    /**
     * Update Personal Details.
     */
    public function updatePersonal(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'nationality' => 'nullable|string|max:100',
            'passport_number' => 'nullable|string|max:20',
            'current_address' => 'nullable|string|max:500',
            'current_city' => 'nullable|string|max:100',
            'current_state' => 'nullable|string|max:100',
            'current_zip' => 'nullable|string|max:10',
            'current_country' => 'nullable|string|max:100',
            'is_permanent_same' => 'boolean',
            'permanent_address' => 'nullable|string|max:500',
            'permanent_city' => 'nullable|string|max:100',
            'permanent_state' => 'nullable|string|max:100',
            'permanent_zip' => 'nullable|string|max:10',
            'permanent_country' => 'nullable|string|max:100',
        ]);

        $employee->personalDetail()->updateOrCreate(
            ['employee_id' => $employee->id],
            $validated
        );

        // Log to activity trail
        $this->activityLogger->log(
            'Personal details updated',
            $employee,
            ['changed_fields' => array_keys($validated)]
        );

        return $this->show($employee);
    }

    /**
     * Update Health Details.
     */
    public function updateHealth(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'blood_group' => 'nullable|string|max:5',
            'height_cm' => 'nullable|numeric|between:50,300',
            'weight_kg' => 'nullable|numeric|between:20,300',
            'allergies' => 'nullable|string|max:1000',
            'chronic_conditions' => 'nullable|string|max:1000',
            'insurance_provider' => 'nullable|string|max:255',
            'policy_number' => 'nullable|string|max:100',
            'last_checkup_date' => 'nullable|date|before_or_equal:today',
            'next_checkup_due' => 'nullable|date|after_or_equal:today',
        ]);

        $employee->healthRecord()->updateOrCreate(
            ['employee_id' => $employee->id],
            $validated
        );

        return $this->show($employee);
    }

   /**
     * Update Bank Details.
     */
    public function updateBank(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:20',
            'account_holder_name' => 'required|string|max:255',
            'bic_code' => 'nullable|string|max:20',
            'account_type' => 'nullable|string|in:savings,current,other',
        ]);

        // Assumes updating the PRIMARY bank account for simplicity in this restore phase
        // or creating one if none exists.
        
        $bank = $employee->bankDetails()->where('is_primary', true)->first();

        if ($bank) {
            $bank->update($validated);
        } else {
            $employee->bankDetails()->create(array_merge($validated, ['is_primary' => true]));
        }

        // Log to activity trail
        $this->activityLogger->log(
            'Bank account details updated',
            $employee,
            ['bank_name' => $validated['bank_name'], 'account_type' => $validated['account_type'] ?? 'savings']
        );

        return $this->show($employee);
    }

    /**
     * Bulk Update Bank Details (CSV)
     */
    public function bulkUpdateBank(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // Skip header

        $count = 0;
        $errors = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            // Mapping: 0:EmpCode, 1:HolderName, 2:BankName, 3:AccNo, 4:IFSC, 5:Branch, 6:AccountType
            if (count($row) < 5) continue;

            $empCode = trim($row[0]);
            $holderName = trim($row[1]);
            $bankName = trim($row[2]);
            $accNo = trim($row[3]);
            $ifsc = trim($row[4]);
            $branch = isset($row[5]) ? trim($row[5]) : null;
            $accType = isset($row[6]) ? strtolower(trim($row[6])) : 'savings';

            $employee = Employee::where('employee_code', $empCode)->first();
            if (!$employee) {
                $errors[] = "Employee with code {$empCode} not found.";
                continue;
            }

            try {
                $employee->bankDetails()->updateOrCreate(
                    ['is_primary' => true],
                    [
                        'account_holder_name' => $holderName,
                        'bank_name' => $bankName,
                        'account_number' => $accNo,
                        'ifsc_code' => $ifsc,
                        'branch_name' => $branch,
                        'account_type' => $accType,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $errors[] = "Error updating {$empCode}: " . $e->getMessage();
            }
        }
        fclose($handle);

        $message = "Successfully updated bank details for {$count} employees.";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " errors occurred.";
        }

        return back()->with('success', $message)->with('errors', $errors);
    }
    /**
     * Store Family Member.
     */
    public function storeFamily(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'dob' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'is_dependent' => 'boolean',
            'is_emergency_contact' => 'boolean'
        ]);

        $employee->families()->create($validated);

        return $this->show($employee);
    }

    /**
     * Update Family Member.
     */
    public function updateFamily(Request $request, Employee $employee, $familyId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'dob' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'is_dependent' => 'boolean',
            'is_emergency_contact' => 'boolean'
        ]);

        $family = $employee->families()->findOrFail($familyId);
        $family->update($validated);

        return $this->show($employee);
    }

    /**
     * Delete Family Member.
     */
    public function destroyFamily(Employee $employee, $familyId)
    {
        $family = $employee->families()->findOrFail($familyId);
        $family->delete();

        return $this->show($employee);
    }
    /**
     * Get Employee Expenses (API).
     */
    public function expenses(Employee $employee)
    {
        $expenses = \App\Models\Expense::with(['category', 'currentStage', 'project'])
            ->where('employee_id', $employee->id)
            ->latest('incurred_date')
            ->get(); // No pagination for simple tab view, or use paginate if list is long. 
            // Tab usually prefers simple list unless huge. Let's return all for now or 50.
        
        return response()->json($expenses);
    }

    
    /**
     * Get Expense Options (Categories, Projects).
     */
    public function expenseOptions()
    {
        return response()->json([
            'categories' => \App\Models\ExpenseCategory::select('id', 'name', 'requires_bill_proof')->where('is_active', true)->get(),
            'projects' => \App\Models\Project::select('id', 'name', 'code')->where('status', 'active')->get()
        ]);
    }

    /**
     * Store Expense for Employee (Admin).
     */
    public function storeExpense(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'incurred_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'is_billable' => 'boolean',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:5120' // 5MB
        ]);

        // Handle Receipt Upload
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expenses', 'public');
        }

        // Get Initial Stage (Submitted) from Expense Workflow
        $workflow = \App\Models\Workflow::where('module', 'Expense')->first();
        if (!$workflow) {
             $workflow = \App\Models\Workflow::where('module', 'Expenses')->first();
        }
        if (!$workflow) {
             $workflow = \App\Models\Workflow::where('module', 'EXPENSE')->first();
        }
        
        $initialStage = $workflow ? $workflow->stages()->orderBy('stage_order', 'asc')->first() : null;

        $categoryName = \App\Models\ExpenseCategory::find($validated['expense_category_id'])->name;

        $expense = $employee->expenses()->create([
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_category_id' => $validated['expense_category_id'],
            'category' => $categoryName, // Populating required string column
            'incurred_date' => $validated['incurred_date'],
            'project_id' => $validated['project_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_billable' => $validated['is_billable'] ?? false,
            'payout_method' => 'payroll', // Default per requirement
            'receipt_path' => $receiptPath,
            'status' => 'Pending', // Admin created, but maybe still needs approval flow? Keeping as Pending default.
            'current_stage_id' => $initialStage ? $initialStage->id : null,
            // 'employee_id' is set via relationship
        ]);

        // Log
        $this->logger->log('expense', 'create', "Admin created expense for {$employee->employee_code}: {$expense->title}");

        return response()->json($expense, 201);
    }
}
