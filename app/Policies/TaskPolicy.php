<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
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
     * Any authenticated active user can list tasks.
     *
     * @param  \App\Models\User  $oUser
     * @return bool
     */
    public function viewAny(User $oUser): bool
    {
        return $oUser->is_active;
    }

    /**
     * Any authenticated active user can view a task.
     *
     * @param  \App\Models\User  $oUser
     * @param  \App\Models\Task  $oTask
     * @return bool
     */
    public function view(User $oUser, Task $oTask): bool
    {
        return $oUser->is_active;
    }

    /**
     * Chef de projet and Développeur roles can create tasks.
     *
     * @param  \App\Models\User  $oUser
     * @return bool
     */
    public function create(User $oUser): bool
    {
        return $oUser->hasAnyRole(['admin', 'chef_projet', 'developpeur']);
    }

    /**
     * A task can be updated by:
     * - The assigned user (can update progress, actual hours).
     * - Chef de projet or admin.
     *
     * @param  \App\Models\User  $oUser
     * @param  \App\Models\Task  $oTask
     * @return bool
     */
    public function update(User $oUser, Task $oTask): bool
    {
        if ($oTask->assigned_to === $oUser->id) {
            return true;
        }

        return $oUser->hasAnyRole(['admin', 'chef_projet']);
    }

    /**
     * Only Chef de projet and admins can delete tasks.
     *
     * @param  \App\Models\User  $oUser
     * @param  \App\Models\Task  $oTask
     * @return bool
     */
    public function delete(User $oUser, Task $oTask): bool
    {
        return $oUser->hasRole('chef_projet');
    }
}
