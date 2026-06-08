<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\Core\ModuleService;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Inertia\Inertia;

class RoleController extends Controller
{
    protected $moduleService;
    protected $logger;

    public function __construct(ModuleService $moduleService, LoggerService $logger)
    {
        $this->moduleService = $moduleService;
        $this->logger = $logger;
    }

    public function index()
    {
        if (!auth()->user()->hasPermission('user_management.role.view_list') && 
            !auth()->user()->hasPermission('user_management.role.view')) {
            abort(403);
        }

        // Filter: Show all roles, including Super Admin as per user request
        $roles = Role::withCount('users')->get();

        return Inertia::render('UserManagement/RoleList', [
            'roles' => RoleResource::collection($roles)
        ]);
    }

    public function matrix()
    {
        if (!auth()->user()->hasPermission('user_management.role.view_matrix') && 
            !auth()->user()->hasPermission('user_management.role.view')) {
             // abort(403);
        }

        // 1. Fetch Roles (Excluding Super Admin)
        $roles = Role::where('name', '!=', 'Super Admin')
            ->with('permissions')
            ->orderBy('id')
            ->get()
            ->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'dashboard' => $role->dashboard,
                    // return a map of "module.submodule.action" => true
                    'permissions' => $role->permissions->mapWithKeys(function($p) {
                         return ["{$p->module}.{$p->submodule}.{$p->action}" => $p->id];
                    })
                ];
            });
        
        // 2. Build Structure for the Grid (Rows)
        // Grouping into Logical Hubs to avoid UI clutter
        $allPermissions = \App\Models\Permission::all();
        
        // Dynamically build logical hubs from active App Modules and App Sub Modules
        $appModules = \App\Models\AppModule::where('status', true)
            ->with(['subModules' => function ($query) {
                $query->where('status', true);
            }])
            ->get();
        
        $logicalHubs = [];
        foreach ($appModules as $appModule) {
            $logicalHubs[$appModule->name] = $appModule->subModules->pluck('key')->toArray();
        }


        // 1. Assign every permission to EXACTLY one hub
        $permissionsWithHub = $allPermissions->map(function($p) use ($logicalHubs) {
            $mod = strtolower(trim($p->module));
            $sub = strtolower(trim($p->submodule));
            $assignedHub = 'Other / Uncategorized'; 
            
            // Priority 1: Exact Submodule Match (Highly specific)
            foreach($logicalHubs as $hubName => $keys) {
                if (in_array($sub, $keys)) {
                    $assignedHub = $hubName;
                    break;
                }
            }
            
            // Priority 2: Exact Module Match (Broad fallback)
            if ($assignedHub === 'Other / Uncategorized') {
                foreach($logicalHubs as $hubName => $keys) {
                    if (in_array($mod, $keys)) {
                        $assignedHub = $hubName;
                        break;
                    }
                }
            }
            
            $p->assigned_hub = $assignedHub;
            return $p;
        });

        // Filter out any permissions that couldn't be strictly mapped to an active UI hub
        // This ensures permissions for inactive (status = false) modules do not show up at the bottom
        $permissionsWithHub = $permissionsWithHub->filter(function($p) {
            return $p->assigned_hub !== 'Other / Uncategorized';
        });

        // 2. Group and build Structure
        $structure = $permissionsWithHub->groupBy('assigned_hub')->map(function($hubPerms, $hubName) {
             return [
                'name' => $hubName,
                'key' => \Illuminate\Support\Str::slug($hubName),
                'submodules' => $hubPerms->groupBy('submodule')->map(function($subPerms, $submodule) {
                    $firstPerm = $subPerms->first();
                    return [
                        'name' => ucfirst(str_replace('_', ' ', $submodule)),
                        'key' => $submodule,
                        'db_module' => $firstPerm ? $firstPerm->module : '', // Actual DB module used for mapping
                        'actions' => $subPerms->pluck('action')->unique()->values()->toArray(),
                        'permission_ids' => $subPerms->mapWithKeys(function($p) {
                            return [$p->action => $p->id];
                        })
                    ];
                })->values()
            ];
        })->values();

        return Inertia::render('UserManagement/PermissionMatrix', [
            'roles' => $roles,
            'structure' => $structure,
            'action_columns' => ['view', 'create', 'update', 'delete'] // Standard columns
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('user_management.role.create')) {
             abort(403);
        }

        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'min:2',
                'max:100', 
                function ($attribute, $value, $fail) {
                    if (strtolower($value) === 'super admin') {
                        $fail('The Super Admin role cannot be created manually.');
                    }
                }
            ],
            // Slug generation usually handled by observer or setter, but keeping simple here
            'description' => 'nullable|string',
            'dashboard' => 'nullable|string|max:255',
        ]);

        $role = Role::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']), // Ensure slug
            'description' => $validated['description'],
            'dashboard' => $validated['dashboard'] ?? '/dashboard',
            'created_by' => auth()->id(),
        ]);

        $this->logger->log('user_management', 'create', "Role created: {$role->name}");

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully');
    }

    public function show(Role $role)
    {
        return new RoleResource($role->load('permissions'));
    }

    public function update(Request $request, Role $role)
    {
        if (!auth()->user()->hasPermission('user_management.role.update')) {
             abort(403);
        }

        if ($role->name === 'Super Admin') {
            return redirect()->back()->with('error', 'Super Admin role cannot be modified')->setStatusCode(303);
        }

        if ($request->has('name') || $request->has('dashboard')) {
            $request->validate([
                 'name' => function ($attribute, $value, $fail) {
                    if ($value && strtolower($value) === 'super admin') {
                        $fail('Cannot rename role to Super Admin.');
                    }
                },
                 'dashboard' => 'nullable|string|max:255',
            ]);
            $role->update($request->only(['name', 'description', 'dashboard']));
        }

        if ($request->has('permissions_matrix')) {
            $matrix = $request->permissions_matrix; 
            
            $allPermissions = DB::table('permissions')->get();
            $permMap = [];
            foreach($allPermissions as $p) {
                $permMap["{$p->module}.{$p->submodule}.{$p->action}"] = $p->id;
            }

            $syncPayload = [];
            
            foreach ($matrix as $item) {
                $key = $item['name'];
                $scope = $item['data_scope'] ?? 'tenant';
                
                if (isset($permMap[$key])) {
                    $pid = $permMap[$key];
                    $syncPayload[$pid] = ['data_scope' => $scope];
                }
            }

            $role->permissions()->sync($syncPayload);
            $this->logger->log('user_management', 'update', "Role permissions updated: {$role->name}");
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        if (!auth()->user()->hasPermission('user_management.role.delete')) {
             abort(403);
        }

        if ($role->is_system || $role->name === 'Super Admin') {
            return redirect()->back()->with('error', 'Cannot delete system/Super Admin role')->setStatusCode(303);
        }

        $role->delete();
        $this->logger->log('user_management', 'delete', "Role deleted: {$role->name}");

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully');
    }

    public function togglePermission(Request $request, Role $role)
    {
        if (!auth()->user()->hasPermission('user_management.role.update')) {
             abort(403);
        }

        if ($role->name === 'Super Admin') {
             abort(403, 'Cannot edit Super Admin');
        }

        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'assign' => 'required|boolean'
        ]);

        if ($request->assign) {
            $role->permissions()->syncWithoutDetaching([$request->permission_id]);
        } else {
            $role->permissions()->detach($request->permission_id);
        }

        return redirect()->back()->setStatusCode(303);
    }
}
