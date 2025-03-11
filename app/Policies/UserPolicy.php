<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return $user->hasPermission('users.view');
    }

    public function view(User $user, User $targetUser)
    {
        if ($user->id == $targetUser->id) {
            return true;
        }

        return $user->hasPermission('users.view');
    }

    public function create(User $user)
    {
        return $user->hasPermission('users.create');
    }

    public function update(User $user, User $targetUser)
    {
        if ($user->id == $targetUser->id) {
            return true;
        }

        return $user->hasPermission('users.edit');
    }

    public function delete(User $user, User $targetUser)
    {
        if ($user->id == $targetUser->id) {
            return true;
        }

        if ($targetUser->isAdmin() && !$user->isAdmin()) {
            return false;
        }

        return $user->hasPermission('users.delete');
    }

    public function manageRoles(User $user)
    {
        return $user->hasPermission('roles.manage');
    }
}
