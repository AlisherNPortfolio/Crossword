<?php

namespace App\Policies;

use App\Models\Crossword;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrosswordPolicy
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
        return true;
    }

    public function view(User $user, Crossword $crossword)
    {
        if ($crossword->published) {
            return true;
        }

        return $user->id == $crossword->created_by || $user->hasPermission('crosswords.view.all');
    }

    public function create(User $user)
    {
        return $user->hasPermission('crosswords.create');
    }

    public function update(User $user, Crossword $crossword)
    {
        if ($user->hasPermission('crosswords.edit.any')) {
            return true;
        }

        return $user->hasPermission('crosswords.edit.own') && $user->id == $crossword->created_by;
    }

    public function delete(User $user, Crossword $crossword)
    {
        if ($user->hasPermission('crosswords.delete.any')) {
            return true;
        }

        return $user->hasPermission('crosswords.delete.own') && $user->id == $crossword->created_by;
    }

    public function publish(User $user, Crossword $crossword)
    {
        if ($user->hasPermission('crosswords.publish')) {
            return false;
        }

        if (!$user->isAdmin() && $user->id != $crossword->created_by) {
            return false;
        }

        return true;
    }
}
