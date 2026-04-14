<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\Infrastructure\LoggerService;

class AuthController extends Controller
{
    /**
     * Mobile Login Handler
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            \Log::context(['email' => $request->email, 'action' => 'mobile_login_failed']);
            LoggerService::error('Mobile Login Failed', [
                'reason' => 'Invalid credentials'
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact HR.'
            ], 403);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        \Log::context(['user_id' => $user->id, 'action' => 'mobile_login_success']);
        LoggerService::info('Mobile Login Successful');

        return response()->json([
            'token' => $token,
            'user' => $this->formatUserResponse($user)
        ]);
    }

    /**
     * Get Authenticated User Profile
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->formatUserResponse($request->user())
        ]);
    }

    /**
     * Mobile Logout Handler
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Handle Sanctum Token Deletion (if applicable)
        if ($user->currentAccessToken() && method_exists($user->currentAccessToken(), 'delete')) {
            $user->currentAccessToken()->delete();
        }

        // Handle Stateful Session Logout (for Inertia/Web sessions on mobile)
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \Log::context(['user_id' => $user->id, 'action' => 'mobile_logout']);
        LoggerService::info('Mobile Logout Successful');

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Format User Response with Permissions and Employee Data
     */
    protected function formatUserResponse(User $user)
    {
        $user->load(['roles.permissions', 'employee.department', 'employee.location']);
        
        $isSuperAdmin = $user->roles->contains('name', 'Super Admin');
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

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first() ? $user->roles->first()->name : 'No Role',
            'avatar' => $user->profile_photo_url,
            'permissions' => $permissions,
            'is_super_admin' => $isSuperAdmin,
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'uuid' => $user->employee->uuid, // Ensure UUID is passed
                'employee_id' => $user->employee->employee_id,
                'designation' => $user->employee->designation,
                'department' => $user->employee->department->name ?? 'N/A',
                'location' => $user->employee->location->name ?? 'N/A',
            ] : null
        ];
    }
}
