<?php

namespace App\Policies;

use App\Models\RequestA4;
use App\Models\Role;
use App\Models\StatusAction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequestA4Policy
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
     * Any authenticated active user can list requests.
     *
     * @param  \App\Models\User  $oUser
     * @return bool
     */
    public function viewAny(User $oUser): bool
    {
        return $oUser->is_active;
    }

    /**
     * Any authenticated active user can view a single request.
     *
     * @param  \App\Models\User   $oUser
     * @param  \App\Models\RequestA4  $oRequestA4
     * @return bool
     */
    public function view(User $oUser, RequestA4 $oRequestA4): bool
    {
        return $oUser->is_active;
    }

    /**
     * Only users with the 'can_create_request' role permission may create a request.
     * Concretely: Chef de projet, Demandeur, or Administrateur.
     *
     * @param  \App\Models\User  $oUser
     * @return bool
     */
    public function create(User $oUser): bool
    {
        return $oUser->hasAnyRole(['admin', 'chef_projet', 'demandeur']);
    }

    /**
     * A request can be updated if:
     * - it is not frozen (or the user is admin — handled by before()), AND
     * - the user has the right role.
     *
     * @param  \App\Models\User       $oUser
     * @param  \App\Models\RequestA4  $oRequestA4
     * @return bool
     */
    public function update(User $oUser, RequestA4 $oRequestA4): bool
    {
        if ($oRequestA4->is_frozen) {
            return false;
        }

        return $oUser->hasAnyRole(['admin', 'chef_projet', 'demandeur']);
    }

    /**
     * Only admins can delete requests (soft-delete).
     * The before() method already grants admin access so this always returns false here.
     *
     * @param  \App\Models\User       $oUser
     * @param  \App\Models\RequestA4  $oRequestA4
     * @return bool
     */
    public function delete(User $oUser, RequestA4 $oRequestA4): bool
    {
        return false; // Only admin (handled by before())
    }

    /**
     * Users with the 'can_export_pdf' role permission can export PDFs.
     * Concretely: Chef de projet, Administrateur.
     *
     * @param  \App\Models\User       $oUser
     * @param  \App\Models\RequestA4  $oRequestA4
     * @return bool
     */
    public function exportPdf(User $oUser, RequestA4 $oRequestA4): bool
    {
        return $oUser->hasAnyRole(['admin', 'chef_projet']);
    }

    /**
     * Verify the user may execute the given StatusAction on the request.
     * Checks:
     * 1. The action belongs to the request's current status.
     * 2. The action is active.
     * 3. The user has at least one of the roles listed on the action (or the action has no role restriction).
     *
     * @param  \App\Models\User       $oUser
     * @param  \App\Models\RequestA4  $oRequestA4
     * @param  \App\Models\StatusAction  $oAction
     * @return bool
     */
    public function executeAction(User $oUser, RequestA4 $oRequestA4, StatusAction $oAction): bool
    {
        // Action must belong to the request's current status
        if ((int) $oAction->status_id !== (int) $oRequestA4->status_id) {
            return false;
        }

        // Action must be active
        if (!$oAction->is_active) {
            return false;
        }

        // No role restriction on the action → any active user can execute
        $oRoles = $oAction->roles;
        if ($oRoles->isEmpty()) {
            return $oUser->is_active;
        }

        // User must have at least one matching role
        return $oRoles->contains(fn(Role $oRole) => $oUser->hasRole($oRole->name));
    }
}
