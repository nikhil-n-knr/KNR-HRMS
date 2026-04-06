<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleDashboardAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Allowed role names for this route
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $roleModel = auth()->user()->roles()->first();
        
        if (!$roleModel) {
            abort(403, 'Unauthorized access: No role assigned.');
        }

        // Super Admin gets access everywhere
        if ($roleModel->name === 'Super Admin') {
            return $next($request);
        }

        // Check if the route they are trying to access matches their assigned dashboard
        // E.g., if they are trying to hit /hr/dashboard, ensure their DB role->dashboard == '/hr/dashboard'
        // OR if they pass the original hardcoded role string fallback
        $assignedHome = !empty($roleModel->dashboard) ? $roleModel->dashboard : '/dashboard';
        $currentPath = '/' . ltrim($request->path(), '/');

        // Allow access if they are hitting their natively assigned dashboard
        if ($currentPath === $assignedHome) {
            return $next($request);
        }

        // Fallback: Check against provided legacy roles string just in case
        if (!empty($roles) && in_array($roleModel->name, $roles)) {
            return $next($request);
        }

        // Block access and redirect to their assigned home
        return redirect($assignedHome)->with('error', 'Unauthorized access. Redirected to your dashboard.');
    }
}
