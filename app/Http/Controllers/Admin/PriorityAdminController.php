<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriorityAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(): View
    {
        $aPriorities = Priority::withCount('requestsA4')->orderBy('sort_order')->paginate(20);

        return view('admin.priorities.index', compact('aPriorities'));
    }

    public function create(): View
    {
        return view('admin.priorities.create');
    }

    public function store(Request $oHttpRequest): RedirectResponse
    {
        $aValidated = $oHttpRequest->validate([
            'name'                   => ['required', 'string', 'max:100', 'unique:priorities,name'],
            'label'                  => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'color'                  => ['nullable', 'string', 'max:20'],
            'icon'                   => ['nullable', 'string', 'max:100'],
            'level'                  => ['nullable', 'integer', 'min:1'],
            'requires_justification' => ['boolean'],
            'is_active'              => ['boolean'],
            'sort_order'             => ['nullable', 'integer', 'min:0'],
        ]);

        $oPriority = Priority::create($aValidated);

        $this->oActivityLogService->log('created', "Priorité créée : {$oPriority->name}", $oPriority);

        return redirect()->route('admin.priorities.index')->with('success', __('messages.priority_created'));
    }

    public function edit(int $iId): View
    {
        $oPriority = Priority::findOrFail($iId);

        return view('admin.priorities.edit', compact('oPriority'));
    }

    public function update(Request $oHttpRequest, int $iId): RedirectResponse
    {
        $oPriority = Priority::findOrFail($iId);

        $aValidated = $oHttpRequest->validate([
            'name'                   => ['required', 'string', 'max:100', "unique:priorities,name,{$iId}"],
            'label'                  => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'color'                  => ['nullable', 'string', 'max:20'],
            'icon'                   => ['nullable', 'string', 'max:100'],
            'level'                  => ['nullable', 'integer', 'min:1'],
            'requires_justification' => ['boolean'],
            'is_active'              => ['boolean'],
            'sort_order'             => ['nullable', 'integer', 'min:0'],
        ]);

        $oPriority->update($aValidated);

        $this->oActivityLogService->log('updated', "Priorité mise à jour : {$oPriority->name}", $oPriority);

        return redirect()->route('admin.priorities.index')->with('success', __('messages.priority_updated'));
    }

    public function destroy(int $iId): RedirectResponse
    {
        $oPriority = Priority::findOrFail($iId);

        $oPriority->delete();

        $this->oActivityLogService->log('deleted', "Priorité supprimée : {$oPriority->name}", $oPriority);

        return redirect()->route('admin.priorities.index')->with('success', __('messages.priority_deleted'));
    }
}
