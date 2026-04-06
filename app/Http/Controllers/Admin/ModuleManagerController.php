<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Services\ModuleAccessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleManagerController extends Controller
{
    protected $moduleAccessService;

    public function __construct(ModuleAccessService $moduleAccessService)
    {
        $this->moduleAccessService = $moduleAccessService;
    }

    /**
     * Show all available modules
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        
        $modules = $this->moduleAccessService->getTenantModules($tenantId);

        return Inertia::render('Admin/Modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Request module activation
     */
    public function requestActivation(Request $request, Module $module)
    {
        $validated = $request->validate([
            'method' => 'required|in:otp,dev,dev_mode',
        ]);

        try {
            $activationRequest = $this->moduleAccessService->requestActivation(
                auth()->user()->tenant_id,
                $module->id,
                auth()->id(),
                $validated['method']
            );

            return response()->json([
                'success' => true,
                'message' => $validated['method'] === 'otp' 
                    ? 'OTP sent to your email. Please check and verify.'
                    : 'Ready for dev activation.',
                'request_id' => $activationRequest->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify OTP and activate module
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:module_activation_requests,id',
            'otp_code' => 'required|string',
        ]);

        try {
            $orgModule = $this->moduleAccessService->verifyAndActivate(
                $validated['request_id'],
                $validated['otp_code']
            );

            return response()->json([
                'success' => true,
                'message' => 'Module activated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Dev mode activation
     */
    public function devActivate(Request $request, Module $module)
    {
        $validated = $request->validate([
            'key' => 'required|string',
        ]);

        try {
            $orgModule = $this->moduleAccessService->devActivate(
                auth()->user()->tenant_id,
                $module->id,
                auth()->id(),
                $validated['key']
            );

            return response()->json([
                'success' => true,
                'message' => 'Module activated successfully in dev mode!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show users with access to a module
     */
    public function users(Module $module)
    {
        $tenantId = auth()->user()->tenant_id;

        // Users with access
        $usersWithAccess = User::where('tenant_id', $tenantId)
            ->whereHas('modules', function ($query) use ($module) {
                $query->where('module_id', $module->id)
                    ->where('is_enabled', true);
            })
            ->with(['modules' => function ($query) use ($module) {
                $query->where('module_id', $module->id);
            }])
            ->get();

        // Users without access
        $usersWithoutAccess = User::where('tenant_id', $tenantId)
            ->whereDoesntHave('modules', function ($query) use ($module) {
                $query->where('module_id', $module->id)
                    ->where('is_enabled', true);
            })
            ->get();

        return Inertia::render('Admin/Modules/Users', [
            'module' => $module,
            'usersWithAccess' => $usersWithAccess,
            'usersWithoutAccess' => $usersWithoutAccess,
        ]);
    }

    /**
     * Grant user access to module
     */
    public function grantAccess(Request $request, Module $module, User $user)
    {
        $validated = $request->validate([
            'module_role' => 'nullable|string',
        ]);

        try {
            $this->moduleAccessService->grantUserAccess(
                $user->id,
                $module->id,
                $validated['module_role'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => "Access granted to {$user->name}",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Revoke user access to module
     */
    public function revokeAccess(Module $module, User $user)
    {
        try {
            $this->moduleAccessService->revokeUserAccess($user->id, $module->id);

            return response()->json([
                'success' => true,
                'message' => "Access revoked from {$user->name}",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update user's module role
     */
    public function updateRole(Request $request, Module $module, User $user)
    {
        $validated = $request->validate([
            'module_role' => 'required|string',
        ]);

        try {
            $this->moduleAccessService->grantUserAccess(
                $user->id,
                $module->id,
                $validated['module_role']
            );

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
