<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\User;

use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    protected $salaryService;

    public function __construct(\App\Services\Payroll\SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    public function index(Request $request)
    {
        $query = Candidate::whereHas('applications', function ($q) {
                $q->whereIn('status', ['Joined', 'Hired', 'Offer Accepted']);
            })
            ->with(['applications.job', 'applications.offerLetter'])
            ->latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return Inertia::render('Talent/Onboarding/Index', [
            'candidates' => $query->paginate(15),
            'filters' => $request->only(['search'])
        ]);
    }

    public function create(Candidate $candidate)
    {
        // Ensure candidate has accepted offer
        $offer = $candidate->applications()->latest()->first()->offerLetter;
        
        if (!$offer || $offer->status !== 'Accepted') {
            return back()->with('error', 'Candidate must have an accepted offer to be onboarded.');
        }

        return Inertia::render('Talent/Onboarding/Create', [
            'candidate' => $candidate->load('applications.offerLetter'),
            'offer' => $offer,
            'departments' => Department::all(),
            'roles' => Role::all(),
            'managers' => User::whereHas('employee')->get(['id', 'name']) // Updated from employeeDetail
        ]);
    }

    public function store(Request $request, Candidate $candidate)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|min:8',
            'joining_date' => 'required|date',
            'designation' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'employee_code' => 'required|unique:employees,employee_code' // Updated table
        ]);

        DB::transaction(function () use ($request, $candidate) {
            // 1. Create User
            $password = $request->password ?? Str::random(10);
            $user = User::create([
                'name' => $candidate->first_name . ' ' . $candidate->last_name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'status' => 'active', // Updated from is_active
                'tenant_id' => 1 // Default or retrieve
            ]);

            // 2. Assign Role
            $user->assignRole($request->role_id); // Spatie syntax prefered if available, else attach

            // 3. Create Employee Record
            $employee = \App\Models\Employee::create([
                'user_id' => $user->id,
                'employee_code' => $request->employee_code,
                'designation' => $request->designation,
                'department_id' => $request->department_id,
                'reporting_to' => $request->manager_id,
                'joining_date' => $request->joining_date,
                'status' => 'active',
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'email' => $request->email,
                'phone' => $candidate->phone,
                'tenant_id' => 1
            ]);

             // 4. Link Address via Personal Details
            if ($candidate->address) {
                 $employee->personalDetail()->create([
                     'current_address' => $candidate->address
                 ]);
            }

            // 5. Update Candidate/Application Status
            $candidate->update(['status' => 'Joined']);
            $latestApp = $candidate->applications()->latest()->first();
            $latestApp->update(['status' => 'Joined']);

            // 6. Notify New Employee of Credentials
            $user->notify(new \App\Notifications\Talent\OnboardingWelcomeNotification($user->name, $password));

            // 7. Assign Salary from Offer (Integration)
            if ($latestApp && $latestApp->offerLetter && $latestApp->offerLetter->salary_amount > 0) {
                $offer = $latestApp->offerLetter;
                
                // Fallback: Pick first active payroll structure if not specified in offer
                $structureId = $offer->salary_structure_id;
                
                if (!$structureId) {
                     $defaultStructure = \App\Models\SalaryStructure::where('is_active', true)->first();
                     $structureId = $defaultStructure ? $defaultStructure->id : null;
                }

                if ($structureId) {
                    $this->salaryService->assignSalary(
                        $employee,
                        $structureId,
                        $offer->salary_amount,
                        $request->joining_date,
                        'Auto-assigned via Offer Integration'
                    );
                }
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Employee Onboarded Successfully!')
            ->setStatusCode(303);
    }
}
