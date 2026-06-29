<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\RequestA4;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    /**
     * Display a listing of tasks with optional filters.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function index(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', Task::class);

        $oQuery = Task::with(['requestA4', 'assignedUser', 'taskType'])
            ->whereNull('deleted_at');

        if ($sSearch = $oHttpRequest->input('search')) {
            $oQuery->where('title', 'like', "%{$sSearch}%");
        }

        if ($sStatus = $oHttpRequest->input('status')) {
            $oQuery->where('status', $sStatus);
        }

        if ($iAssignedTo = $oHttpRequest->input('assigned_to')) {
            $oQuery->where('assigned_to', $iAssignedTo);
        }

        if ($iRequestId = $oHttpRequest->input('request_a4_id')) {
            $oQuery->where('request_a4_id', $iRequestId);
        }

        $oTasks    = $oQuery->orderBy('end_date')->paginate(20)->withQueryString();
        $aUsers    = User::where('is_active', true)->orderBy('name')->get();
        $aStatuses = ['pending', 'in_progress', 'done', 'cancelled'];

        return view('tasks.index', compact('oTasks', 'aUsers', 'aStatuses'));
    }

    /**
     * Show the form to create a new task.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function create(Request $oHttpRequest): View
    {
        $this->authorize('create', Task::class);

        $aTaskTypes  = TaskType::orderBy('name')->get();
        $aUsers      = User::where('is_active', true)->orderBy('name')->get();
        $aRequests   = RequestA4::whereNull('deleted_at')
            ->whereHas('status', fn($q) => $q->where('is_final', false))
            ->orderBy('reference')
            ->get(['id', 'reference', 'title']);

        // Pre-select a request if provided via query string
        $iPreselectedRequestId = (int) $oHttpRequest->input('request_a4_id');

        // All tasks available as potential dependencies
        $aAllTasks = Task::whereNull('deleted_at')
            ->orderBy('title')
            ->get(['id', 'title', 'request_a4_id']);

        return view('tasks.create', compact('aTaskTypes', 'aUsers', 'aRequests', 'iPreselectedRequestId', 'aAllTasks'));
    }

    /**
     * Store a newly created task.
     *
     * @param  \App\Http\Requests\StoreTaskRequest  $oRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTaskRequest $oRequest): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $aValidated             = $oRequest->validated();
        $aValidated['created_by'] = Auth::id();

        $aDependencyIds = $aValidated['dependency_ids'] ?? [];
        unset($aValidated['dependency_ids']);

        $oTask = Task::create($aValidated);

        if (!empty($aDependencyIds)) {
            $oTask->dependencies()->sync($aDependencyIds);
        }

        $this->oActivityLogService->log('created', "Tâche créée : {$oTask->title}", $oTask, [], $oTask->toArray());

        return redirect()
            ->route('tasks.show', $oTask->id)
            ->with('success', __('messages.task_created'));
    }

    /**
     * Display the specified task.
     *
     * @param  int  $iId
     * @return \Illuminate\View\View
     */
    public function show(int $iId): View
    {
        $oTask = Task::with([
            'requestA4', 'taskType', 'assignedUser', 'createdBy',
            'timeEntries.user', 'dependencies', 'dependents', 'attachments',
        ])->findOrFail($iId);

        $this->authorize('view', $oTask);

        return view('tasks.show', compact('oTask'));
    }

    /**
     * Show the form for editing the specified task.
     *
     * @param  int  $iId
     * @return \Illuminate\View\View
     */
    public function edit(int $iId): View
    {
        $oTask = Task::findOrFail($iId);

        $this->authorize('update', $oTask);

        $aTaskTypes = TaskType::orderBy('name')->get();
        $aUsers     = User::where('is_active', true)->orderBy('name')->get();
        $aRequests  = RequestA4::whereNull('deleted_at')
            ->orderBy('reference')
            ->get(['id', 'reference', 'title']);

        // Available tasks for dependency selection (exclude self and its dependents)
        $aAllTasks = Task::whereNull('deleted_at')
            ->where('id', '!=', $oTask->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('tasks.edit', compact('oTask', 'aTaskTypes', 'aUsers', 'aRequests', 'aAllTasks'));
    }

    /**
     * Update the specified task.
     *
     * @param  \App\Http\Requests\UpdateTaskRequest  $oRequest
     * @param  int                                   $iId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateTaskRequest $oRequest, int $iId): RedirectResponse
    {
        $oTask = Task::findOrFail($iId);

        $this->authorize('update', $oTask);

        $aOldValues     = $oTask->toArray();
        $aValidated     = $oRequest->validated();

        $aDependencyIds = $aValidated['dependency_ids'] ?? [];
        unset($aValidated['dependency_ids']);

        $oTask->update($aValidated);
        $oTask->dependencies()->sync($aDependencyIds);

        $this->oActivityLogService->log('updated', "Tâche mise à jour : {$oTask->title}", $oTask, $aOldValues, $oTask->fresh()->toArray());

        return redirect()
            ->route('tasks.show', $oTask->id)
            ->with('success', __('messages.task_updated'));
    }

    /**
     * Soft-delete the specified task.
     *
     * @param  int  $iId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $iId): RedirectResponse
    {
        $oTask = Task::findOrFail($iId);

        $this->authorize('delete', $oTask);

        $this->oActivityLogService->log('deleted', "Tâche supprimée : {$oTask->title}", $oTask);

        $oTask->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', __('messages.task_deleted'));
    }

    /**
     * Display the global Gantt chart across all tasks/users.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function gantt(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', Task::class);

        $aUsers = User::where('is_active', true)->orderBy('name')->get();

        // Default to current user if no filter
        $iFilterUserId = $oHttpRequest->input('user_id', Auth::id());

        return view('tasks.gantt', compact('aUsers', 'iFilterUserId'));
    }

    /**
     * Display the Gantt chart for a specific request (or request-scoped task context).
     *
     * @param  int  $iId  Task ID used as a pivot to find its parent request.
     * @return \Illuminate\View\View
     */
    public function ganttByRequest(int $iId): View
    {
        $oTask = Task::findOrFail($iId);

        $this->authorize('view', $oTask);

        $oRequestA4 = $oTask->requestA4;

        if ($oRequestA4) {
            $this->authorize('view', $oRequestA4);
        }

        return view('tasks.gantt-request', compact('oTask', 'oRequestA4'));
    }
}
