<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\ModuleService;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class NavigationController extends Controller
{
    use ApiResponser;

    protected $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Get the filtered navigation tree for the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return $this->error('Unauthorized', 401);

        $allModules = $this->moduleService->getActiveModules();
        $filteredMenu = [];

        foreach ($allModules as $module) {
            
            // 1. Check Module-Level Logic
            // If module itself has a hard requirement (unlikely in current DB, but good practice)
            // if ($module->permission && !$user->hasPermission($module->permission)) continue;

            $visibleSubModules = [];

            // 2. Filter Sub-Modules
            foreach ($module->sub_modules as $sub) {
                // Construct standard permission Key: "module.submodule.view"
                // Or "view_list" - let's check basic access
                
                $viewKey = "{$module->key}.{$sub->key}.view";
                $listKey = "{$module->key}.{$sub->key}.view_list";
                $createKey = "{$module->key}.{$sub->key}.create";
                
                // Allow if user has ANY standard access
                if ($user->hasPermission($viewKey) || $user->hasPermission($listKey) || $user->hasPermission($createKey)) {
                    $visibleSubModules[] = $sub;
                    continue;
                }

                // Explicit overrides for specific modules if naming convention differs
                 // e.g. Access Review might be "user_management.access_review.view"
                 // Our loop above covers that since access_review key is "access_review"
            }

            // 3. Fallback: If no permissions required (e.g. Dashboard), or if it's open
            if ($module->sub_modules->isEmpty()) {
                // For main items like Dashboard, usually unrestricted
                 // Or add a logic: if($module->key === 'dashboard') $visibleSubModules = true;
                 $filteredMenu[] = $module;
            } elseif (!empty($visibleSubModules)) {
                // Clone module to avoid mutating cache reference
                // Use a simple array structure for response
                $modArray = (array) $module;
                $modArray['sub_modules'] = $visibleSubModules;
                $filteredMenu[] = $modArray;
            }
        }

        return $this->success([
            'menu' => $filteredMenu
        ]);
    }
}
