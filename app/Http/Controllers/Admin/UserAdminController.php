<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', User::class);

        $oUsers  = User::withTrashed()
            ->with('teams')
            ->orderBy('name')
            ->paginate(25);
        $aTeams  = Team::orderBy('name')->get();

        return view('admin.users.index', compact('oUsers', 'aTeams'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $aRoles = Role::orderBy('name')->get();
        $aTeams = Team::orderBy('name')->get();

        return view('admin.users.create', compact('aRoles', 'aTeams'));
    }

    public function store(Request $oHttpRequest): RedirectResponse
    {
        $this->authorize('create', User::class);

        $aValidated = $oHttpRequest->validate([
            'name'       => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'is_active'  => ['boolean'],
        ]);

        $aValidated['password'] = Hash::make($aValidated['password']);

        $oUser = User::create($aValidated);

        $this->oActivityLogService->log('created', "Utilisateur créé : {$oUser->email}", $oUser);

        return redirect()->route('admin.users.index')->with('success', __('messages.user_created'));
    }

    public function show(int $iId): View
    {
        $oUser = User::withTrashed()->with(['teams', 'teamUserRoles.role', 'teamUserRoles.team'])->findOrFail($iId);

        $this->authorize('view', $oUser);

        return view('admin.users.show', compact('oUser'));
    }

    public function edit(int $iId): View
    {
        $oUser = User::findOrFail($iId);

        $this->authorize('update', $oUser);

        $aRoles = Role::orderBy('name')->get();
        $aTeams = Team::orderBy('name')->get();

        return view('admin.users.edit', compact('oUser', 'aRoles', 'aTeams'));
    }

    public function update(Request $oHttpRequest, int $iId): RedirectResponse
    {
        $oUser = User::findOrFail($iId);

        $this->authorize('update', $oUser);

        $aRules = [
            'name'       => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['required', 'email', "unique:users,email,{$iId}"],
            'phone'      => ['nullable', 'string', 'max:20'],
            'is_active'  => ['boolean'],
        ];

        if ($oHttpRequest->filled('password')) {
            $aRules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $aValidated = $oHttpRequest->validate($aRules);

        if (!empty($aValidated['password'])) {
            $aValidated['password'] = Hash::make($aValidated['password']);
        } else {
            unset($aValidated['password']);
        }

        $oUser->update($aValidated);

        $this->oActivityLogService->log('updated', "Utilisateur mis à jour : {$oUser->email}", $oUser);

        return redirect()->route('admin.users.index')->with('success', __('messages.user_updated'));
    }

    public function destroy(int $iId): RedirectResponse
    {
        $oUser = User::findOrFail($iId);

        $this->authorize('delete', $oUser);

        $oUser->delete();

        $this->oActivityLogService->log('deleted', "Utilisateur supprimé : {$oUser->email}", $oUser);

        return redirect()->route('admin.users.index')->with('success', __('messages.user_deleted'));
    }
}
