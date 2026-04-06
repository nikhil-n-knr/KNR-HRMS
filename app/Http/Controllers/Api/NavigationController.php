<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NavigationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        \Illuminate\Support\Facades\Log::info("Navigation Access for user: " . $user->email . " (ID: " . $user->id . ")");

        // 1. Strict Role Gates (For critical modules)
        // Super Admin always bypasses this check.
        $roleGates = [
            'user_management' => ['Super Admin', 'Admin'],
            'org'             => ['Super Admin', 'Admin'],
            'settings'        => ['Super Admin', 'Admin'],
            
            // Sub Modules
            // Attendance permissions are managed via Permission Matrix UI
            
            // HR & Payroll
            'hr_payroll' => ['Super Admin', 'Admin', 'HR', 'Manager'],
            'hr_payroll.payroll' => ['Super Admin', 'Admin', 'HR'],
            'hr_payroll.disbursement' => ['Super Admin', 'Admin', 'HR', 'Finance'],
            'hr_payroll.tax_config' => ['Super Admin', 'Admin', 'HR'],
            'hr_payroll.tax_declarations' => ['Super Admin', 'Admin', 'HR'],
            
            // Project Management
            'project_management.clients.view' => ['Super Admin', 'Admin', 'Manager'],
            'project_management.planner.view' => ['Super Admin', 'Admin', 'Manager'],
            'project_management.bug_intelligence' => ['Super Admin', 'Admin', 'Manager'],
            'project_management.pulse' => ['Super Admin', 'Admin', 'Manager'],
            'project_management.pending_approvals' => ['Super Admin', 'Admin', 'Manager'],
            
            // System Admin
            'system_admin' => ['Super Admin', 'Admin', 'Manager'],
            'system_admin.modules' => ['Super Admin', 'Admin'],

            // DevOps
            'devops_link' => ['Super Admin', 'Admin', 'Manager'],
            
            // Security
            'security_identity' => ['Super Admin', 'Admin', 'Security Manager'],
            'cms' => ['Super Admin', 'Admin', 'Manager'],
            'advanced_lms' => ['Super Admin', 'Admin', 'Manager'],
        ];

        // Fetch Modules from DB with Submodules
        $modules = \App\Models\AppModule::with(['subModules' => function($q) {
            $q->where('status', true)->orderBy('order');
        }])->where('status', true)->orderBy('order')->get();

        $menu = [];
        $isSuperAdmin = $user->hasRole('Super Admin');

        foreach ($modules as $mod) {
            // 1. Module Level Check
            $canViewModule = false;
            
            if ($isSuperAdmin) {
                $canViewModule = true;
            } else {
                // Check Role Gate OR Permission
                if (isset($roleGates[$mod->key])) {
                    if ($user->hasRole($roleGates[$mod->key])) {
                        $canViewModule = true;
                    }
                }
                
                if (!$canViewModule && $user->hasPermission($mod->key . '.view')) {
                    $canViewModule = true;
                }

                // If still not allowed, check if any of its submodules are viewable (via Permission OR Role)
                if (!$canViewModule) {
                    foreach ($mod->subModules as $subCheck) {
                        $subPermKey = $mod->key . '.' . $subCheck->key;
                        if ($user->hasPermission($subPermKey . '.view')) {
                            $canViewModule = true;
                            break;
                        }
                        if (isset($roleGates[$subPermKey]) && $user->hasRole($roleGates[$subPermKey])) {
                            $canViewModule = true;
                            break;
                        }
                    }
                    
                    // Structural module with no submodules fallback
                    if (!$canViewModule && $mod->subModules->isEmpty() && !empty($mod->route)) {
                        $canViewModule = true;
                    }
                }
            }

            if (!$canViewModule) continue;

            $item = [
                'id' => $mod->id,
                'key' => $mod->key,
                'name' => $mod->name,
                'icon' => $mod->icon, 
                'route' => $mod->route,
                'sidebar_group' => $mod->sidebar_group ?: 'Main Menu',
                'sub_modules' => []
            ];

            // 2. SubModule Check
            foreach ($mod->subModules as $sub) {
                $canViewSub = false;
                $permKey = $mod->key . '.' . $sub->key;

                if ($isSuperAdmin) {
                    $canViewSub = true;
                } else {
                    // Check Role Gate OR Permission
                    if (isset($roleGates[$permKey])) {
                        if ($user->hasRole($roleGates[$permKey])) {
                            $canViewSub = true;
                        }
                    }
                    
                    if (!$canViewSub && ($user->hasPermission($permKey . '.view') || $user->hasPermission($mod->key . '.view'))) {
                        $canViewSub = true;
                    }
                }

                if ($canViewSub) {
                    $resolvedRoute = null;

                    // Standard resolution logic for common keys
                    if ($mod->key === 'org' && $sub->key === 'departments') $resolvedRoute = route('admin.departments.index');
                    if ($mod->key === 'org' && $sub->key === 'locations') $resolvedRoute = route('admin.locations.index');
                    
                    if ($mod->key === 'user_management') {
                         if ($sub->key === 'user' || $sub->key === 'users') $resolvedRoute = route('admin.users.index');
                         if ($sub->key === 'role' || $sub->key === 'roles') $resolvedRoute = route('admin.roles.index');
                         if ($sub->key === 'permission' || $sub->key === 'permissions') $resolvedRoute = route('admin.roles.matrix');
                    }
                    if ($mod->key === 'crm' && $sub->key === 'crm_hub') $resolvedRoute = route('crm.hub');

                    // Fallback to route field in DB
                    if (!$resolvedRoute && !empty($sub->route)) {
                        if (str_starts_with($sub->route, '/')) {
                            $resolvedRoute = url($sub->route);
                        } else if (\Illuminate\Support\Facades\Route::has($sub->route)) {
                            try {
                                $resolvedRoute = route($sub->route);
                            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                                $resolvedRoute = $sub->route;
                            } catch (\Exception $e) {
                                $resolvedRoute = $sub->route;
                            }
                        } else {
                            try {
                                // Sometimes the route name exists but Route::has fails for dynamic setups
                                $resolvedRoute = route($sub->route);
                            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                                $resolvedRoute = '#'; // Safe fallback
                            } catch (\Exception $e) {
                                $resolvedRoute = '#';
                            }
                        }
                    }

                    $subItem = [
                        'id' => $sub->id,
                        'key' => $sub->key,
                        'name' => $sub->name,
                        'route' => $resolvedRoute ?: '#'
                    ];

                    $item['sub_modules'][] = $subItem;
                }
            }

            // Cleanup Empty Parents
            if (empty($item['route']) && empty($item['sub_modules'])) {
                 continue;
            }

            // Resolve Parent Route
            if (!empty($item['route'])) {
                if (str_starts_with($item['route'], '/')) {
                    $item['route'] = url($item['route']);
                } else if (\Illuminate\Support\Facades\Route::has($item['route'])) {
                    try {
                        $item['route'] = route($item['route']);
                    } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                         // Safe ignore, fallback to raw DB value
                    } catch (\Exception $e) { }
                } else {
                     try {
                         $item['route'] = route($item['route']);
                     } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                         $item['route'] = '#';
                     } catch (\Exception $e) { }
                }
            }

            $menu[] = $item;
        }

        return response()->json([
            'data' => [
                'menu' => $menu
            ]
        ]);
    }

}
