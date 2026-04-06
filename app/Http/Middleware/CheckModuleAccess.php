<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ModuleAccessService;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    protected $moduleAccessService;

    public function __construct(ModuleAccessService $moduleAccessService)
    {
        $this->moduleAccessService = $moduleAccessService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleName): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has access to the module
        if (!$this->moduleAccessService->userHasAccess($user->id, $moduleName)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => "You don't have access to the {$moduleName} module."
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', "You don't have access to the {$moduleName} module. Please contact your administrator.");
        }

        return $next($request);
    }
}
