<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(): View
    {
        $aCompanies = Company::withCount('requestsA4')->orderBy('name')->paginate(20);

        return view('admin.companies.index', compact('aCompanies'));
    }

    public function create(): View
    {
        return view('admin.companies.create');
    }

    public function store(Request $oHttpRequest): RedirectResponse
    {
        $aValidated = $oHttpRequest->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:companies,name'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'code'       => ['nullable', 'string', 'max:20'],
            'is_active'  => ['boolean'],
        ]);

        $oCompany = Company::create($aValidated);

        $this->oActivityLogService->log('created', "Société créée : {$oCompany->name}", $oCompany);

        return redirect()->route('admin.companies.index')->with('success', __('messages.company_created'));
    }

    public function edit(int $iId): View
    {
        $oCompany = Company::findOrFail($iId);

        return view('admin.companies.edit', compact('oCompany'));
    }

    public function update(Request $oHttpRequest, int $iId): RedirectResponse
    {
        $oCompany = Company::findOrFail($iId);

        $aValidated = $oHttpRequest->validate([
            'name'       => ['required', 'string', 'max:255', "unique:companies,name,{$iId}"],
            'short_name' => ['nullable', 'string', 'max:50'],
            'code'       => ['nullable', 'string', 'max:20'],
            'is_active'  => ['boolean'],
        ]);

        $oCompany->update($aValidated);

        $this->oActivityLogService->log('updated', "Société mise à jour : {$oCompany->name}", $oCompany);

        return redirect()->route('admin.companies.index')->with('success', __('messages.company_updated'));
    }

    public function destroy(int $iId): RedirectResponse
    {
        $oCompany = Company::findOrFail($iId);

        $oCompany->delete();

        $this->oActivityLogService->log('deleted', "Société supprimée : {$oCompany->name}", $oCompany);

        return redirect()->route('admin.companies.index')->with('success', __('messages.company_deleted'));
    }
}
