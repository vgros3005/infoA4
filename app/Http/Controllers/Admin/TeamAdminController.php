<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamUserRole;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamAdminController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Team::class);

        $oTeams = Team::withCount('users')->orderBy('name')->paginate(20);

        return view('admin.teams.index', compact('oTeams'));
    }

    public function create(): View
    {
        $this->authorize('create', Team::class);

        $aUsers = User::where('is_active', true)->orderBy('name')->get();
        $aRoles = Role::orderBy('name')->get();

        return view('admin.teams.create', compact('aUsers', 'aRoles'));
    }

    public function store(Request $oHttpRequest): RedirectResponse
    {
        $this->authorize('create', Team::class);

        $aValidated = $oHttpRequest->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:teams,name'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $oTeam = Team::create($aValidated);

        $this->oActivityLogService->log('created', "Équipe créée : {$oTeam->name}", $oTeam);

        return redirect()->route('admin.teams.index')->with('success', __('messages.team_created'));
    }

    public function show(int $iId): View
    {
        $oTeam = Team::with(['users', 'teamUserRoles.user', 'teamUserRoles.role'])->findOrFail($iId);

        $this->authorize('view', $oTeam);

        $aAvailableUsers = User::where('is_active', true)->orderBy('name')->get();
        $aRoles          = Role::orderBy('name')->get();

        return view('admin.teams.show', compact('oTeam', 'aAvailableUsers', 'aRoles'));
    }

    public function edit(int $iId): View
    {
        $oTeam = Team::findOrFail($iId);

        $this->authorize('update', $oTeam);

        $aAvailableUsers = User::where('is_active', true)->orderBy('name')->get();
        $aRoles          = Role::orderBy('name')->get();

        return view('admin.teams.edit', compact('oTeam', 'aAvailableUsers', 'aRoles'));
    }

    public function update(Request $oHttpRequest, int $iId): RedirectResponse
    {
        $oTeam = Team::findOrFail($iId);

        $this->authorize('update', $oTeam);

        $aValidated = $oHttpRequest->validate([
            'name'        => ['required', 'string', 'max:255', "unique:teams,name,{$iId}"],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $oTeam->update($aValidated);

        $this->oActivityLogService->log('updated', "Équipe mise à jour : {$oTeam->name}", $oTeam);

        return redirect()->route('admin.teams.index')->with('success', __('messages.team_updated'));
    }

    public function destroy(int $iId): RedirectResponse
    {
        $oTeam = Team::findOrFail($iId);

        $this->authorize('delete', $oTeam);

        $oTeam->delete();

        $this->oActivityLogService->log('deleted', "Équipe supprimée : {$oTeam->name}", $oTeam);

        return redirect()->route('admin.teams.index')->with('success', __('messages.team_deleted'));
    }

    public function memberStore(Request $oHttpRequest, int $iTeamId): RedirectResponse
    {
        $oTeam = Team::findOrFail($iTeamId);
        $this->authorize('update', $oTeam);

        $aValidated = $oHttpRequest->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        TeamUserRole::firstOrCreate(
            ['team_id' => $oTeam->id, 'user_id' => $aValidated['user_id']],
            ['role_id' => $aValidated['role_id']]
        );

        $this->oActivityLogService->log('updated', "Membre ajouté à l'équipe {$oTeam->name}", $oTeam);

        return back()->with('success', __('messages.member_added'));
    }

    public function memberUpdate(Request $oHttpRequest, int $iTeamId, int $iTurId): RedirectResponse
    {
        $oTeam = Team::findOrFail($iTeamId);
        $this->authorize('update', $oTeam);

        $aValidated = $oHttpRequest->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        TeamUserRole::where('id', $iTurId)->where('team_id', $oTeam->id)->update($aValidated);

        return back()->with('success', __('messages.member_updated'));
    }

    public function memberDestroy(int $iTeamId, int $iTurId): RedirectResponse
    {
        $oTeam = Team::findOrFail($iTeamId);
        $this->authorize('update', $oTeam);

        TeamUserRole::where('id', $iTurId)->where('team_id', $oTeam->id)->delete();

        $this->oActivityLogService->log('updated', "Membre retiré de l'équipe {$oTeam->name}", $oTeam);

        return back()->with('success', __('messages.member_removed'));
    }
}
