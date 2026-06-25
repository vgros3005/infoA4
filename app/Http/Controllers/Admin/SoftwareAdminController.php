<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Software;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoftwareAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(): View
    {
        $aSoftwares = Software::withCount('requestsA4')->orderBy('name')->paginate(20);

        return view('admin.softwares.index', compact('aSoftwares'));
    }

    public function create(): View
    {
        return view('admin.softwares.create');
    }

    public function store(Request $oHttpRequest): RedirectResponse
    {
        $aValidated = $oHttpRequest->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:softwares,name'],
            'version'     => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $oSoftware = Software::create($aValidated);

        $this->oActivityLogService->log('created', "Logiciel créé : {$oSoftware->name}", $oSoftware);

        return redirect()->route('admin.softwares.index')->with('success', __('messages.software_created'));
    }

    public function edit(int $iId): View
    {
        $oSoftware = Software::findOrFail($iId);

        return view('admin.softwares.edit', compact('oSoftware'));
    }

    public function update(Request $oHttpRequest, int $iId): RedirectResponse
    {
        $oSoftware = Software::findOrFail($iId);

        $aValidated = $oHttpRequest->validate([
            'name'        => ['required', 'string', 'max:255', "unique:softwares,name,{$iId}"],
            'version'     => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $oSoftware->update($aValidated);

        $this->oActivityLogService->log('updated', "Logiciel mis à jour : {$oSoftware->name}", $oSoftware);

        return redirect()->route('admin.softwares.index')->with('success', __('messages.software_updated'));
    }

    public function destroy(int $iId): RedirectResponse
    {
        $oSoftware = Software::findOrFail($iId);

        $oSoftware->delete();

        $this->oActivityLogService->log('deleted', "Logiciel supprimé : {$oSoftware->name}", $oSoftware);

        return redirect()->route('admin.softwares.index')->with('success', __('messages.software_deleted'));
    }
}
