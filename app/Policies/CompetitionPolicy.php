<?php

namespace App\Policies;

use App\Models\Competition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompetitionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Competition $competition)
    {
        if ($competition->is_active) {
            return true;
        }

        return $user->hasPermission('competitions.view.all');
    }

    public function create(User $user)
    {
        return $user->hasPermission('competitions.create');
    }

    public function update(User $user, Competition $competition) {
        if ($user->hasPermission('competitions.edit.any')) {
            return true;
        }

        $crossword = $competition->crossword;
        $isCreator = $crossword && $user->id == $crossword->created_by;

        return $user->hasPermission('competitions.edit.own') && $isCreator;
    }

    public function delete(User $user, Competition $competition) {
        if ($user->hasPermission('competitions.delete.any')) {
            return true;
        }

        $crossword = $competition->crossword;
        $isCreator = $crossword && $user->id == $crossword->created_by;

        return $user->hasPermission('competitions.delete.own') && $isCreator;
    }

    public function terminate(User $user, Competition $competition)
    {
        if (!$user->hasPermission('competitions.terminate')) {
            return false;
        }

        if (!$competition->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $crossword = $competition->crossword;
        return $crossword && $user->id == $crossword->created_by;
    }
}
