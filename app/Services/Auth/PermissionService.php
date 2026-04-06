<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * Check if a user has a specific permission.
     * 
     * @param User $user
     * @param string $permissionName dot notation (module.submodule.action)
     * @return bool
     */
    public function hasPermission(User $user, string $permissionName): bool
    {
        // 1. Check for Super Admin (optional, maybe via a role)
        // if ($user->isSuperAdmin()) return true;

        // 2. Resolve permission ID from name
        // Ideally we cache map of permission_name -> id
        $permissionId = $this->getPermissionIdByName($permissionName);
        if (!$permissionId) return false;

        // 3. Check cached effective permissions
        $permissions = $this->getEffectivePermissions($user);

        return $permissions->contains('id', $permissionId);
    }

    /**
     * Get all effective permissions for a user (merged from roles + scopes)
     * 
     * @param User $user
     * @return Collection
     */
    public function getEffectivePermissions(User $user): Collection
    {
        return Cache::remember("user_permissions_{$user->id}", 3600, function () use ($user) {
            $user->load(['roles.permissions', 'scopes.permission']);

            $permissions = collect();

            // 1. Role Permissions
            foreach ($user->roles as $role) {
                if (!$role->is_active && $role->pivot->is_active == false) continue; // Check active assignment

                // Check pivot dates
                $now = now();
                if ($role->pivot->valid_from && $role->pivot->valid_from > $now) continue;
                if ($role->pivot->valid_until && $role->pivot->valid_until < $now) continue;

                foreach ($role->permissions as $perm) {
                    // Add to collection, key by ID to avoid duplicates?
                    // We need to keep the 'pivot' info (scope) which might differ.
                    // Strategy: Merge permissions, taking the most permissive scope if duplicated.
                    
                    $existing = $permissions->get($perm->id);
                    if ($existing) {
                        // Merge logic: maximize scope (custom logic required)
                        // For now, simpler invalidation or overwrite
                        $permissions->put($perm->id, $this->mergePermissionScopes($existing, $perm));
                    } else {
                        // Attach scope info to permission object
                        $perm->scope = $role->pivot->data_scope;
                        $permissions->put($perm->id, $perm);
                    }
                }
            }

            // 2. User Scope Overrides (Grant or Revoke?) 
            // Current DB schema 'user_scope' seems to GRANT specific scopes. 
            // If it's an override, it replaces role permission.
            foreach ($user->scopes as $scope) {
                if ($scope->valid_until && $scope->valid_until < now()) continue;
                
                $perm = $scope->permission;
                $perm->scope = $scope->data_scope;
                $permissions->put($perm->id, $perm);
            }

            return $permissions;
        });
    }

    private function getPermissionIdByName(string $name): ?int
    {
        return Cache::remember("perm_id_{$name}", 86400, function () use ($name) {
            $parts = explode('.', $name);
            if (count($parts) != 3) return null;
            
            $p = Permission::where('module', $parts[0])
                ->where('submodule', $parts[1])
                ->where('action', $parts[2])
                ->first();
                
            return $p ? $p->id : null;
        });
    }

    private function mergePermissionScopes($perm1, $perm2)
    {
        // Simple hierarchy: global > tenant > location > department > team > self
        $hierarchy = ['global' => 6, 'tenant' => 5, 'location' => 4, 'department' => 3, 'team' => 2, 'self' => 1];
        
        $s1 = $hierarchy[$perm1->scope] ?? 0;
        $s2 = $hierarchy[$perm2->scope ?? 'self'] ?? 0; // Role perm has scope in pivot

        // If reusing object, ensure we don't mutate the original cached reference incorrectly if not cloned
        // But here we are building a transient collection.
        
        if ($s2 > $s1) {
            return $perm2;
        }
        return $perm1;
    }
}
