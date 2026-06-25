<?php

namespace App\Policies;

use App\Models\TaskTimeEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskTimeEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Admins bypass all policy checks.
     *
     * @param  \App\Models\User  $oUser
     * @param  string            $sAbility
     * @return bool|null
     */
    public function before(User $oUser, string $sAbility): ?bool
    {
        if ($oUser->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Any authenticated active user can view the list (filtered in controller).
     *
     * @param  \App\Models\User  $oUser
     * @return bool
     */
    public function viewAny(User $oUser): bool
    {
        return $oUser->is_active;
    }

    /**
     * A user can view their own time entries; admins see all.
     *
     * @param  \App\Models\User         $oUser
     * @param  \App\Models\TaskTimeEntry  $oEntry
     * @return bool
     */
    public function view(User $oUser, TaskTimeEntry $oEntry): bool
    {
        return $oEntry->user_id === $oUser->id;
    }

    /**
     * Any active authenticated user can create a time entry.
     *
     * @param  \App\Models\User  $oUser
     * @return bool
     */
    public function create(User $oUser): bool
    {
        return $oUser->is_active;
    }

    /**
     * A user can only edit their own time entries.
     *
     * @param  \App\Models\User         $oUser
     * @param  \App\Models\TaskTimeEntry  $oEntry
     * @return bool
     */
    public function update(User $oUser, TaskTimeEntry $oEntry): bool
    {
        return $oEntry->user_id === $oUser->id;
    }

    /**
     * A user can only delete their own time entries.
     *
     * @param  \App\Models\User         $oUser
     * @param  \App\Models\TaskTimeEntry  $oEntry
     * @return bool
     */
    public function delete(User $oUser, TaskTimeEntry $oEntry): bool
    {
        return $oEntry->user_id === $oUser->id;
    }
}
