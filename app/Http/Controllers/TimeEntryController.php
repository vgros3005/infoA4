<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Requests\UpdateTimeEntryRequest;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    /**
     * Display a listing of time entries with user/period filters.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function index(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', TaskTimeEntry::class);

        /** @var \App\Models\User $oCurrentUser */
        $oCurrentUser = Auth::user();

        $oQuery = TaskTimeEntry::with(['task.requestA4', 'user']);

        // Non-admins can only see their own entries
        if (!$oCurrentUser->isAdmin()) {
            $oQuery->where('user_id', $oCurrentUser->id);
        } elseif ($iUserId = $oHttpRequest->input('user_id')) {
            $oQuery->where('user_id', $iUserId);
        }

        // Period filter
        if ($sDateFrom = $oHttpRequest->input('date_from')) {
            $oQuery->where('entry_date', '>=', $sDateFrom);
        }
        if ($sDateTo = $oHttpRequest->input('date_to')) {
            $oQuery->where('entry_date', '<=', $sDateTo);
        }

        // Task filter
        if ($iTaskId = $oHttpRequest->input('task_id')) {
            $oQuery->where('task_id', $iTaskId);
        }

        $oEntries    = $oQuery->orderByDesc('entry_date')->paginate(25)->withQueryString();
        $nTotalHours = $oQuery->sum('hours');

        $oUsers = $oCurrentUser->isAdmin()
            ? User::where('is_active', true)->orderBy('name')->get()
            : collect([$oCurrentUser]);

        // Tâches de l'utilisateur courant pour le formulaire de saisie rapide
        $aMyTasks = Task::with('requestA4')
            ->where('assigned_to', $oCurrentUser->id)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNull('deleted_at')
            ->orderBy('end_date')
            ->get();

        return view('time-entries.index', compact('oEntries', 'nTotalHours', 'oUsers', 'aMyTasks'));
    }

    /**
     * Show the form to create a new time entry.
     * Pre-loads the current user's assigned tasks.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function create(Request $oHttpRequest): RedirectResponse
    {
        // The entry form is embedded in the index page; redirect there
        $iTaskId = $oHttpRequest->input('task_id');
        return redirect()->route('time-entries.index', $iTaskId ? ['task_id' => $iTaskId] : []);
    }

    /**
     * Store a newly created time entry.
     *
     * @param  \App\Http\Requests\StoreTimeEntryRequest  $oRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTimeEntryRequest $oRequest): RedirectResponse
    {
        $this->authorize('create', TaskTimeEntry::class);

        $aValidated            = $oRequest->validated();
        $aValidated['user_id'] = Auth::id();

        $oEntry = TaskTimeEntry::create($aValidated);

        $this->oActivityLogService->log(
            'created',
            "Temps saisi : {$oEntry->hours}h sur tâche #{$oEntry->task_id} le {$oEntry->entry_date->toDateString()}",
            $oEntry,
            [],
            $oEntry->toArray()
        );

        return redirect()
            ->route('time-entries.index')
            ->with('success', __('messages.time_entry_created'));
    }

    /**
     * Display the specified time entry.
     *
     * @param  int  $iId
     * @return \Illuminate\View\View
     */
    public function show(int $iId): View
    {
        $oEntry = TaskTimeEntry::with(['task.requestA4', 'user'])->findOrFail($iId);

        $this->authorize('view', $oEntry);

        return view('time-entries.show', compact('oEntry'));
    }

    /**
     * Show the form for editing the specified time entry.
     *
     * @param  int  $iId
     * @return \Illuminate\View\View
     */
    public function edit(int $iId): View
    {
        $oEntry = TaskTimeEntry::findOrFail($iId);

        $this->authorize('update', $oEntry);

        /** @var \App\Models\User $oCurrentUser */
        $oCurrentUser = Auth::user();

        $oAllTasks = Task::with('requestA4')
            ->where('assigned_to', $oEntry->user_id)
            ->whereNull('deleted_at')
            ->orderByDesc('start_date')
            ->get();

        return view('time-entries.edit', compact('oEntry', 'oAllTasks'));
    }

    /**
     * Update the specified time entry.
     *
     * @param  \App\Http\Requests\UpdateTimeEntryRequest  $oRequest
     * @param  int                                         $iId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateTimeEntryRequest $oRequest, int $iId): RedirectResponse
    {
        $oEntry = TaskTimeEntry::findOrFail($iId);

        $this->authorize('update', $oEntry);

        $aOldValues = $oEntry->toArray();

        $oEntry->update($oRequest->validated());

        $this->oActivityLogService->log(
            'updated',
            "Temps mis à jour : {$oEntry->hours}h sur tâche #{$oEntry->task_id}",
            $oEntry,
            $aOldValues,
            $oEntry->fresh()->toArray()
        );

        return redirect()
            ->route('time-entries.index')
            ->with('success', __('messages.time_entry_updated'));
    }

    /**
     * Delete the specified time entry.
     *
     * @param  int  $iId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $iId): RedirectResponse
    {
        $oEntry = TaskTimeEntry::findOrFail($iId);

        $this->authorize('delete', $oEntry);

        $this->oActivityLogService->log(
            'deleted',
            "Temps supprimé : {$oEntry->hours}h sur tâche #{$oEntry->task_id} le {$oEntry->entry_date->toDateString()}",
            $oEntry
        );

        $oEntry->delete();

        return redirect()
            ->route('time-entries.index')
            ->with('success', __('messages.time_entry_deleted'));
    }
}
