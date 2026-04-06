<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('user_management.role.view_list') || 
               $user->hasPermission('user_management.role.view');
    }

    public function view(User $user, Role $role)
    {
        return $user->hasPermission('user_management.role.view_detail') || 
               $user->hasPermission('user_management.role.view');
    }

    public function create(User $user)
    {
        return $user->hasPermission('user_management.role.create');
    }

    public function update(User $user, Role $role)
    {
        return $user->hasPermission('user_management.role.update');
    }

    public function delete(User $user, Role $role)
    {
        return $user->hasPermission('user_management.role.delete');
    }
}
