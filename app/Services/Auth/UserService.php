<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class UserService
{
    /**
     * Create a new user with tenant context.
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Hash password
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Defaults
            $data['status'] = $data['status'] ?? 'active';
            
            // Allow tenant_id to be passed or inferred from auth (validation should handle security)
            if (!isset($data['tenant_id']) && auth()->check()) {
                $data['tenant_id'] = auth()->user()->tenant_id;
            }

            // Create User
            $user = User::create($data);

            // Assign Default Role if any
            if (isset($data['role_ids'])) {
                foreach ($data['role_ids'] as $roleId) {
                    $role = Role::find($roleId);
                    if ($role) {
                        $this->assignRole($user, $role);
                    }
                }
            }

            return $user;
        });
    }

    public function assignRole(User $user, Role $role, array $meta = []): void
    {
        // Prevent duplicate assignment active
        // Logic: attach or sync?
        // user_role table has unique constraint on (user_id, role_id)
        
        $assignedBy = auth()->id() ?? 1; // System fallback

        $user->roles()->attach($role->id, [
            'assigned_by' => $assignedBy,
            'valid_from' => $meta['valid_from'] ?? now(),
            'valid_until' => $meta['valid_until'] ?? null,
            'is_active' => true,
        ]);
        
        // Invalidate cache
        $this->clearPermissionCache($user);
    }

    public function removeRole(User $user, Role $role): void
    {
        $user->roles()->detach($role->id);
        $this->clearPermissionCache($user);
    }

    private function clearPermissionCache(User $user)
    {
        \Illuminate\Support\Facades\Cache::forget("user_permissions_{$user->id}");
    }
}
