<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // 1. Super Admin Bypass
        if ($user->roles->contains('name', 'Super Admin') || $user->roles->contains('name', 'Admin')) {
            // Note: 'Admin' bypass might be desired for Manager routes too
            return $next($request);
        }

        // 2. Check for ANY of the required roles
        foreach ($roles as $role) {
            // Support pipe for OR logic within a single parameter (e.g. role:Manager|Admin)
            $subRoles = explode('|', $role);
            
            foreach ($subRoles as $subRole) {
                if ($user->roles->contains('name', trim($subRole))) {
                    return $next($request);
                }
            }
        }

        \Illuminate\Support\Facades\Log::error('CheckRole Abort 403', [
            'url' => $request->fullUrl(),
            'route' => $request->route() ? $request->route()->getName() : null,
            'roles_required' => $roles,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10),
        ]);

        abort(403, 'Unauthorized. Required Role: ' . implode(', ', $roles));
    }
}
