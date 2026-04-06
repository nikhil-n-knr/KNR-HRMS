<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user() ?: Auth::guard('client')->user();
        $userData = null;
        $profileUrl = null;

        if ($user) {
             if ($user instanceof \App\Models\ClientUser) {
                 $userData = $user->toArray();
                 $userData['is_client'] = true;
                 $userData['permissions'] = ['client.access']; // Default for client users
                 $profileUrl = null; // Client profile edit not yet specific
             } else {
                 $user->load(['roles.permissions', 'employee']);
                 $isSuperAdmin = $user->is_super_admin ?? $user->roles->contains('name', 'Super Admin');
                 
                 $permissions = [];
                 if ($isSuperAdmin) {
                     $permissions = ['*']; 
                 } else {
                     $permissions = $user->roles->flatMap(function($role) {
                        return $role->permissions->map(function($p) {
                            return "{$p->module}.{$p->submodule}.{$p->action}";
                        });
                    })->unique()->values()->toArray();
                 }

                 $userData = $user->toArray();
                 $userData['capabilities'] = $permissions;
                 $userData['is_super_admin'] = (bool)$isSuperAdmin;
                 $userData['role'] = $user->roles->first(); 

                 // Robustness: Attempt to resolve profile URL even if direct relationship is broken
                 // The logic for profileUrl is now moved into a closure directly in the 'auth' array.
                 // The original $employee and $profileUrl calculation here is removed.
             }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData ? [
                    ...$userData,
                    'permissions' => $userData['capabilities'] ?? [], // Expose permissions explicitly for frontend
                ] : null,
                'profileUrl' => function () use ($request) {
                    $user = $request->user() ?: Auth::guard('client')->user();
                    if (!$user) return null;

                    if ($user instanceof \App\Models\ClientUser) {
                        return \Illuminate\Support\Facades\Route::has('portal.dashboard') 
                            ? route('portal.dashboard') 
                            : url('/portal/dashboard');
                    }

                    $employee = $user->employee;
                    if (!$employee && $user->email) {
                        $employee = \App\Models\Employee::where('email', $user->email)->first();
                    }

                    if ($employee && $employee->uuid) {
                        return route('employee.profile', $employee->uuid);
                    }

                    return \Illuminate\Support\Facades\Route::has('profile.edit') 
                        ? route('profile.edit') 
                        : url('/profile');
                },
                'unreadNotificationsCount' => $request->user() ? $request->user()->unreadNotifications()->count() : 0,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'offer_data' => fn () => $request->session()->get('offer_data'),
            ],
        ];
    }
}
