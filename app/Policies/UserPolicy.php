<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('user_management.users.view_list') || 
               $user->hasPermission('user_management.users.view');
    }

    public function view(User $user, User $model)
    {
        return $user->hasPermission('user_management.users.view_detail') || 
               $user->hasPermission('user_management.users.view');
    }

    public function create(User $user)
    {
        return $user->hasPermission('user_management.users.create');
    }

    public function update(User $user, User $model)
    {
        return $user->hasPermission('user_management.users.update');
    }

    public function delete(User $user, User $model)
    {
        return $user->hasPermission('user_management.users.delete');
    }
}
