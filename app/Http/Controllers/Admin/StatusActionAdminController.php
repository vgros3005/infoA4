<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Status;
use App\Models\StatusAction;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusActionAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(int $iStatusId): View
    {
        $oStatus  = Status::findOrFail($iStatusId);
        $oActions = StatusAction::with('roles', 'targetStatus')
            ->where('status_id', $iStatusId)
            ->orderBy('sort_order')
            ->get();

        return view('admin.status-actions.index', compact('oStatus', 'oActions'));
    }

    public function create(int $iStatusId): View
    {
        $oStatus   = Status::findOrFail($iStatusId);
        $oStatuses = Status::orderBy('sort_order')->get();
        $oRoles    = Role::orderBy('name')->get();

        return view('admin.status-actions.create', compact('oStatus', 'oStatuses', 'oRoles'));
    }

    public function store(Request $oHttpRequest, int $iStatusId): RedirectResponse
    {
        $oStatus = Status::findOrFail($iStatusId);

        $aValidated = $oHttpRequest->validate([
            'action_name'      => ['required', 'string', 'max:100'],
            'action_label'     => ['required', 'string', 'max:255'],
            'target_status_id' => ['required', 'integer', 'exists:statuses,id'],
            'button_color'     => ['nullable', 'string', 'max:20'],
            'icon'             => ['nullable', 'string', 'max:100'],
            'requires_comment' => ['boolean'],
            'is_active'        => ['boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'role_ids'         => ['nullable', 'array'],
            'role_ids.*'       => ['integer', 'exists:roles,id'],
        ]);

        $aRoleIds = $aValidated['role_ids'] ?? [];
        unset($aValidated['role_ids']);

        $aValidated['status_id'] = $iStatusId;

        $oAction = StatusAction::create($aValidated);
        $oAction->roles()->sync($aRoleIds);

        $this->oActivityLogService->log('created', "Action de statut créée : {$oAction->action_name}", $oAction);

        return redirect()
            ->route('admin.status-actions.index', $iStatusId)
            ->with('success', __('messages.status_action_created'));
    }

    public function edit(int $iStatusId, int $iId): View
    {
        $oStatus   = Status::findOrFail($iStatusId);
        $oAction   = StatusAction::with('roles')->findOrFail($iId);
        $oStatuses = Status::orderBy('sort_order')->get();
        $oRoles    = Role::orderBy('name')->get();

        return view('admin.status-actions.edit', compact('oStatus', 'oAction', 'oStatuses', 'oRoles'));
    }

    public function update(Request $oHttpRequest, int $iStatusId, int $iId): RedirectResponse
    {
        $oStatus = Status::findOrFail($iStatusId);
        $oAction = StatusAction::findOrFail($iId);

        $aValidated = $oHttpRequest->validate([
            'action_name'      => ['required', 'string', 'max:100'],
            'action_label'     => ['required', 'string', 'max:255'],
            'target_status_id' => ['required', 'integer', 'exists:statuses,id'],
            'button_color'     => ['nullable', 'string', 'max:20'],
            'icon'             => ['nullable', 'string', 'max:100'],
            'requires_comment' => ['boolean'],
            'is_active'        => ['boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'role_ids'         => ['nullable', 'array'],
            'role_ids.*'       => ['integer', 'exists:roles,id'],
        ]);

        $aRoleIds = $aValidated['role_ids'] ?? [];
        unset($aValidated['role_ids']);

        $oAction->update($aValidated);
        $oAction->roles()->sync($aRoleIds);

        $this->oActivityLogService->log('updated', "Action de statut mise à jour : {$oAction->action_name}", $oAction);

        return redirect()
            ->route('admin.status-actions.index', $iStatusId)
            ->with('success', __('messages.status_action_updated'));
    }

    public function destroy(int $iStatusId, int $iId): RedirectResponse
    {
        $oAction = StatusAction::findOrFail($iId);

        $oAction->delete();

        $this->oActivityLogService->log('deleted', "Action de statut supprimée : {$oAction->action_name}", $oAction);

        return redirect()
            ->route('admin.status-actions.index', $iStatusId)
            ->with('success', __('messages.status_action_deleted'));
    }
}
