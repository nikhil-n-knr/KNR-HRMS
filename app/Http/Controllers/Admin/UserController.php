<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Inertia\Inertia;

class UserController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        // 1. Authorization
        if (!auth()->user()->hasPermission('user_management.users.view_list') && 
            !auth()->user()->hasPermission('user_management.users.view')) {
            abort(403);
        }

        // 2. Resolve Scope
        $scope = auth()->user()->getPermissionScope('user_management.users.view_list') 
              ?? auth()->user()->getPermissionScope('user_management.users.view');

        $query = User::with(['roles', 'department', 'location']);

        // 3. Apply Scope Logic
        switch ($scope) {
            case 'global':
            case 'tenant':
                $query->where('tenant_id', auth()->user()->tenant_id);
                break;
            case 'department':
                $query->where('department_id', auth()->user()->department_id);
                break;
            case 'team':
                $query->where('manager_id', auth()->id());
                break;
            case 'self':
            default:
                $query->where('id', auth()->id());
                break;
        }

        // 4. Filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('id', $request->role_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('UserManagement/UserList', [
            'users' => UserResource::collection($users),
            'filters' => $request->only(['search', 'role_id', 'status'])
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('user_management.users.create')) {
            abort(403);
        }

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users',
            'employee_id'       => 'nullable|unique:users',
            'employee_link_id'  => 'nullable|exists:employees,id',
            'role_ids'          => 'required|array',
            'role_ids.*'        => 'exists:roles,id',
            'department_id'     => 'nullable|exists:departments,id',
            'location_id'       => 'nullable|exists:locations,id',
            'password'          => 'required|min:8',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'tenant_id'     => auth()->user()->tenant_id,
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'employee_id'   => $validated['employee_id'] ?? null,
                'password'      => Hash::make($validated['password']),
                'department_id' => $validated['department_id'] ?? null,
                'location_id'   => $validated['location_id'] ?? null,
                'status'        => 'active',
            ]);

            // Link the employee record to this user account
            if (!empty($validated['employee_link_id'])) {
                \App\Models\Employee::where('id', $validated['employee_link_id'])
                    ->whereNull('user_id')  // Safety: never overwrite an existing link
                    ->update(['user_id' => $user->id]);
            }

            $pivotData = [
                'assigned_by' => auth()->id(),
                'valid_from'  => now(),
                'is_active'   => true
            ];
            
            $syncPayload = collect($validated['role_ids'])->mapWithKeys(function ($id) use ($pivotData) {
                return [$id => $pivotData];
            })->toArray();

            $user->roles()->sync($syncPayload);

            $this->logger->log('user_management', 'create', "User created: {$user->email}");
        });

        return redirect()->back()->with('success', 'User created successfully');
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('user_management.users.create')) {
            abort(403);
        }

        // Employees that have no user linked yet
        $unlinkedEmployees = \App\Models\Employee::whereNull('user_id')
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->orderBy('first_name')
            ->get()
            ->map(fn($e) => [
                'id'   => $e->id,
                'name' => "{$e->first_name} {$e->last_name} ({$e->employee_code})"
            ]);

        return Inertia::render('UserManagement/UserForm', [
            'roles'              => \App\Models\Role::all(),
            'departments'        => \App\Models\Department::select('id', 'name')->get(),
            'locations'          => \App\Models\Location::select('id', 'name')->get(),
            'unlinkedEmployees'  => $unlinkedEmployees,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('user_management.users.update')) {
            abort(403);
        }

        $validated = $request->validate([
            'name'              => 'sometimes|string|max:255',
            'email'             => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'employee_id'       => ['nullable', Rule::unique('users')->ignore($user->id)],
            'employee_link_id'  => 'nullable|exists:employees,id',
            'department_id'     => 'nullable|exists:departments,id',
            'role_ids'          => 'sometimes|array',
            'role_ids.*'        => 'exists:roles,id',
            'location_id'       => 'nullable|exists:locations,id',
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            if ($request->filled('password')) {
                $validatedPassword = $request->validate(['password' => 'min:8']);
                $user->password = Hash::make($validatedPassword['password']);
                $user->save();
            }

            $user->update($request->only(['name', 'email', 'department_id', 'location_id', 'status']));

            // Handle employee (re)linking
            if (array_key_exists('employee_link_id', $validated)) {
                $newEmpId = $validated['employee_link_id'];

                // Detach old linked employee
                \App\Models\Employee::where('user_id', $user->id)->update(['user_id' => null]);

                // Attach the new one (if one was selected)
                if ($newEmpId) {
                    \App\Models\Employee::where('id', $newEmpId)
                        ->whereNull('user_id')
                        ->update(['user_id' => $user->id]);
                }
            }

            if ($request->has('role_ids')) {
                $pivotData = [
                    'assigned_by' => auth()->id(),
                    'valid_from'  => now(),
                    'is_active'   => true
                ];
                
                $syncPayload = collect($request->role_ids)->mapWithKeys(function ($id) use ($pivotData) {
                    return [$id => $pivotData];
                })->toArray();
                
                $user->roles()->sync($syncPayload);
            }

            $this->logger->log('user_management', 'update', "User updated: {$user->email}");
        });

        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function edit(User $user)
    {
        if (!auth()->user()->hasPermission('user_management.users.update')) {
            abort(403);
        }

        // Currently linked employee (if any)
        $linkedEmployee = \App\Models\Employee::where('user_id', $user->id)
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->first();

        // Employees that have no user linked yet (+ the currently linked one so it shows in dropdown)
        $unlinkedEmployees = \App\Models\Employee::where(function($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->orderBy('first_name')
            ->get()
            ->map(fn($e) => [
                'id'   => $e->id,
                'name' => "{$e->first_name} {$e->last_name} ({$e->employee_code})"
            ]);

        return Inertia::render('UserManagement/UserForm', [
            'user'               => $user->load('roles'),
            'roles'              => \App\Models\Role::all(),
            'departments'        => \App\Models\Department::select('id', 'name')->get(),
            'locations'          => \App\Models\Location::select('id', 'name')->get(),
            'unlinkedEmployees'  => $unlinkedEmployees,
            'linkedEmployeeId'   => $linkedEmployee?->id,
        ]);
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->hasPermission('user_management.users.delete')) {
            abort(403);
        }
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Cannot delete yourself');
        }

        $user->delete();
        $this->logger->log('user_management', 'delete', "User deleted: {$user->email}");

        return redirect()->back()->with('success', 'User deleted successfully');
    }

    public function accessReview(Request $request)
    {
        if (!auth()->user()->hasPermission('user_management.users.view_access_review') && 
            !auth()->user()->hasPermission('user_management.users.view')) {
             // abort(403);
        }

        $accessReport = null;
        $selectedUser = null;

        if ($request->has('user_id')) {
            $selectedUser = User::with(['roles.permissions', 'department'])->find($request->user_id);
            
            if ($selectedUser) {
                // Calculate Effective Permissions
                // 1. Get all unique permissions from all roles
                $allUserPermissions = collect();
                
                // Super Admin Check
                $isSuperAdmin = $selectedUser->roles->contains('name', 'Super Admin');

                foreach ($selectedUser->roles as $role) {
                    foreach ($role->permissions as $perm) {
                        $key = "{$perm->module}.{$perm->submodule}.{$perm->action}";
                        // If already exists, maybe merge scope (taking widest)? 
                        // For now, just simplistic unique check.
                        if (!$allUserPermissions->has($key)) {
                            $allUserPermissions->put($key, [
                                'module' => $perm->module,
                                'submodule' => $perm->submodule,
                                'action' => $perm->action,
                                'scope' => $perm->pivot->data_scope ?? 'tenant'
                            ]);
                        }
                    }
                }

                // 2. Build Matrix structure for Report
                // Similar to RoleController but we just need the "Result"
                // We want to show ALL modules and highlight what they HAVE.
                // So fetch all permissions first.
                $systemPermissions = \App\Models\Permission::all();
                
                $matrix = $systemPermissions->groupBy('module')->map(function($subModules, $moduleName) use ($allUserPermissions, $isSuperAdmin) {
                    return [
                        'name' => ucfirst(str_replace('_', ' ', $moduleName)),
                        'sub_modules' => $subModules->groupBy('submodule')->map(function($actions, $subName) use ($allUserPermissions, $isSuperAdmin) {
                            return [
                                'name' => ucfirst(str_replace('_', ' ', $subName)),
                                'permissions' => $actions->map(function($perm) use ($allUserPermissions, $isSuperAdmin) {
                                    $key = "{$perm->module}.{$perm->submodule}.{$perm->action}";
                                    $hasPerm = $isSuperAdmin || $allUserPermissions->has($key);
                                    $assigned = $allUserPermissions->get($key);
                                    
                                    return [
                                        'action' => $perm->action,
                                        'allowed' => $hasPerm,
                                        'scope' => $hasPerm ? ($isSuperAdmin ? 'global' : ($assigned['scope'] ?? 'tenant')) : null
                                    ];
                                })->values()
                            ];
                        })->values()
                    ];
                })->values();

                $accessReport = [
                    'user' => [
                        'name' => $selectedUser->name,
                        'email' => $selectedUser->email,
                        'avatar' => $selectedUser->profile_photo_url, // Assuming standard Laravel
                        'status' => $selectedUser->status ?? 'Active'
                    ],
                    'roles' => $selectedUser->roles->pluck('name'),
                    'access_matrix' => $matrix
                ];
            }
        }

        return Inertia::render('UserManagement/AccessReview', [
            'users' => User::select('id', 'name as first_name', 'email')->orderBy('name')->paginate(50), // Simplified list for dropdown
            'selectedUser' => $selectedUser,
            'accessReport' => $accessReport
        ]);
    }
    public function search(Request $request)
    {
        $query = $request->get('query');
        
        $users = User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('employee_id', 'like', "%{$query}%")
                    ->limit(10)
                    ->get(['id', 'name', 'email']); 
                    
        return response()->json($users);
    }
}
