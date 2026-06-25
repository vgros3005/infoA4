<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestType;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestTypeAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(): View
    {
        $aRequestTypes = RequestType::withCount('requestsA4')->orderBy('name')->paginate(20);

        return view('admin.request-types.index', compact('aRequestTypes'));
    }

    public function create(): View
    {
        return view('admin.request-types.create');
    }

    public function store(Request $oHttpRequest): RedirectResponse
    {
        $aValidated = $oHttpRequest->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:request_types,name'],
            'label'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $oType = RequestType::create($aValidated);

        $this->oActivityLogService->log('created', "Type de demande créé : {$oType->name}", $oType);

        return redirect()->route('admin.request-types.index')->with('success', __('messages.request_type_created'));
    }

    public function edit(int $iId): View
    {
        $oType = RequestType::findOrFail($iId);

        return view('admin.request-types.edit', compact('oType'));
    }

    public function update(Request $oHttpRequest, int $iId): RedirectResponse
    {
        $oType = RequestType::findOrFail($iId);

        $aValidated = $oHttpRequest->validate([
            'name'        => ['required', 'string', 'max:100', "unique:request_types,name,{$iId}"],
            'label'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $oType->update($aValidated);

        $this->oActivityLogService->log('updated', "Type de demande mis à jour : {$oType->name}", $oType);

        return redirect()->route('admin.request-types.index')->with('success', __('messages.request_type_updated'));
    }

    public function destroy(int $iId): RedirectResponse
    {
        $oType = RequestType::findOrFail($iId);

        $oType->delete();

        $this->oActivityLogService->log('deleted', "Type de demande supprimé : {$oType->name}", $oType);

        return redirect()->route('admin.request-types.index')->with('success', __('messages.request_type_deleted'));
    }
}
