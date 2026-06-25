<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Status;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Status::class);

        $aStatuses = Status::with(['actions.roles'])->orderBy('sort_order')->get();
        $aRoles    = Role::orderBy('name')->get();

        return view('admin.statuses.index', compact('aStatuses', 'aRoles'));
    }

    public function create(): View
    {
        $this->authorize('create', Status::class);

        return view('admin.statuses.create');
    }

    public function store(Request $oHttpRequest): RedirectResponse
    {
        $this->authorize('create', Status::class);

        $aValidated = $oHttpRequest->validate([
            'name'            => ['required', 'string', 'max:100', 'unique:statuses,name'],
            'label'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'color'           => ['nullable', 'string', 'max:20'],
            'icon'            => ['nullable', 'string', 'max:100'],
            'is_initial'      => ['boolean'],
            'is_final'        => ['boolean'],
            'freezes_request' => ['boolean'],
            'generates_pdf'   => ['boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
        ]);

        $oStatus = Status::create($aValidated);

        $this->oActivityLogService->log('created', "Statut créé : {$oStatus->name}", $oStatus);

        return redirect()->route('admin.statuses.index')->with('success', __('messages.status_created'));
    }

    public function show(int $iId): View
    {
        $oStatus = Status::with('actions.roles')->findOrFail($iId);

        $this->authorize('view', $oStatus);

        return view('admin.statuses.show', compact('oStatus'));
    }

    public function edit(int $iId): View
    {
        $oStatus = Status::findOrFail($iId);

        $this->authorize('update', $oStatus);

        return view('admin.statuses.edit', compact('oStatus'));
    }

    public function update(Request $oHttpRequest, int $iId): RedirectResponse
    {
        $oStatus = Status::findOrFail($iId);

        $this->authorize('update', $oStatus);

        $aValidated = $oHttpRequest->validate([
            'name'            => ['required', 'string', 'max:100', "unique:statuses,name,{$iId}"],
            'label'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'color'           => ['nullable', 'string', 'max:20'],
            'icon'            => ['nullable', 'string', 'max:100'],
            'is_initial'      => ['boolean'],
            'is_final'        => ['boolean'],
            'freezes_request' => ['boolean'],
            'generates_pdf'   => ['boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
        ]);

        $oStatus->update($aValidated);

        $this->oActivityLogService->log('updated', "Statut mis à jour : {$oStatus->name}", $oStatus);

        return redirect()->route('admin.statuses.index')->with('success', __('messages.status_updated'));
    }

    public function destroy(int $iId): RedirectResponse
    {
        $oStatus = Status::findOrFail($iId);

        $this->authorize('delete', $oStatus);

        $oStatus->delete();

        $this->oActivityLogService->log('deleted', "Statut supprimé : {$oStatus->name}", $oStatus);

        return redirect()->route('admin.statuses.index')->with('success', __('messages.status_deleted'));
    }
}
