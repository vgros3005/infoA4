<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestA4Request;
use App\Http\Requests\UpdateRequestA4Request;
use App\Models\Company;
use App\Models\Priority;
use App\Models\RequestA4;
use App\Models\RequestType;
use App\Models\Software;
use App\Models\Status;
use App\Models\StatusAction;
use App\Models\Team;
use App\Services\ActivityLogService;
use App\Services\PdfService;
use App\Services\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RequestA4Controller extends Controller
{
    public function __construct(
        private readonly WorkflowService    $oWorkflowService,
        private readonly PdfService         $oPdfService,
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    /**
     * Display a filtered/paginated listing of requests.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function index(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', RequestA4::class);

        $oQuery = RequestA4::with(['status', 'priority', 'requester', 'requestType'])
            ->whereNull('deleted_at');

        // --- Text search ---
        if ($sSearch = $oHttpRequest->input('search')) {
            $oQuery->where(function ($q) use ($sSearch) {
                $q->where('title', 'like', "%{$sSearch}%")
                  ->orWhere('reference', 'like', "%{$sSearch}%")
                  ->orWhere('description', 'like', "%{$sSearch}%");
            });
        }

        // --- Status filter ---
        if ($iStatusId = $oHttpRequest->input('status_id')) {
            $oQuery->where('status_id', $iStatusId);
        }

        // --- Type filter ---
        if ($iTypeId = $oHttpRequest->input('request_type_id')) {
            $oQuery->where('request_type_id', $iTypeId);
        }

        // --- Priority filter ---
        if ($iPriorityId = $oHttpRequest->input('priority_id')) {
            $oQuery->where('priority_id', $iPriorityId);
        }

        // --- Date range filter (requested_date) ---
        if ($sDateFrom = $oHttpRequest->input('date_from')) {
            $oQuery->where('requested_date', '>=', $sDateFrom);
        }
        if ($sDateTo = $oHttpRequest->input('date_to')) {
            $oQuery->where('requested_date', '<=', $sDateTo);
        }

        $oRequests   = $oQuery->orderByDesc('created_at')->paginate(20)->withQueryString();
        $aStatuses   = Status::orderBy('sort_order')->get();
        $aTypes      = RequestType::orderBy('name')->get();
        $aPriorities = Priority::where('is_active', true)->orderBy('sort_order')->get();

        return view('requests.index', compact('oRequests', 'aStatuses', 'aTypes', 'aPriorities'));
    }

    /**
     * Show the form for creating a new request.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $this->authorize('create', RequestA4::class);

        $aRequestTypes = RequestType::orderBy('name')->get();
        $aPriorities   = Priority::where('is_active', true)->orderBy('sort_order')->get();
        $aCompanies    = Company::orderBy('name')->get();
        $aSoftwares    = Software::orderBy('name')->get();
        $aStatuses     = Status::where('is_initial', true)->orderBy('sort_order')->get();
        $aTeams        = Team::orderBy('name')->get();

        return view('requests.create', compact('aRequestTypes', 'aPriorities', 'aCompanies', 'aSoftwares', 'aStatuses', 'aTeams'));
    }

    /**
     * Store a newly created request in storage.
     *
     * @param  \App\Http\Requests\StoreRequestA4Request  $oRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequestA4Request $oRequest): RedirectResponse
    {
        $this->authorize('create', RequestA4::class);

        $aValidated = $oRequest->validated();

        // Extract relation arrays before mass assignment
        $aCompanyIds  = $aValidated['company_ids']  ?? [];
        $aSoftwareIds = $aValidated['software_ids'] ?? [];
        unset($aValidated['company_ids'], $aValidated['software_ids']);

        // Set requester and initial status
        $aValidated['requester_id'] = Auth::id();

        if (empty($aValidated['status_id'])) {
            $oInitialStatus = Status::where('is_initial', true)->first();
            $aValidated['status_id'] = $oInitialStatus?->id;
        }

        $oRequestA4 = RequestA4::create($aValidated);

        // Sync many-to-many
        $oRequestA4->companies()->sync($aCompanyIds);
        $oRequestA4->softwares()->sync($aSoftwareIds);

        // Handle file attachments
        if ($oRequest->hasFile('attachments')) {
            foreach ($oRequest->file('attachments') as $oFile) {
                $sPath = $oFile->store('attachments/requests', 'private');
                $oRequestA4->attachments()->create([
                    'filename'       => $oFile->getClientOriginalName(),
                    'path'           => $sPath,
                    'mime_type'      => $oFile->getMimeType(),
                    'size'           => $oFile->getSize(),
                    'uploaded_by'    => Auth::id(),
                ]);
            }
        }

        $this->oActivityLogService->log(
            'created',
            "Fiche A4 créée : {$oRequestA4->reference} - {$oRequestA4->title}",
            $oRequestA4,
            [],
            $oRequestA4->toArray()
        );

        return redirect()
            ->route('requests.show', $oRequestA4->id)
            ->with('success', __('messages.request_created', ['ref' => $oRequestA4->reference]));
    }

    /**
     * Display the specified request with history, tasks, and attachments.
     *
     * @param  int  $iId
     * @return \Illuminate\View\View
     */
    public function show(int $iId): View
    {
        $oRequestA4 = RequestA4::with([
            'status.actions.roles',
            'priority',
            'requestType',
            'requester',
            'assignedTeam',
            'companies',
            'softwares',
            'statusHistories.user',
            'statusHistories.fromStatus',
            'statusHistories.toStatus',
            'tasks.assignedUser',
            'tasks.taskType',
            'attachments.uploader',
            'pdfVersions.uploader',
        ])->findOrFail($iId);

        $this->authorize('view', $oRequestA4);

        /** @var \App\Models\User $oCurrentUser */
        $oCurrentUser    = Auth::user();
        $aAvailableActions = $oRequestA4->getAvailableActionsForUser($oCurrentUser);
        $aStatusHistory    = $oRequestA4->statusHistories()->with(['fromStatus', 'toStatus', 'user'])
            ->orderByDesc('created_at')->get();
        $oRequest = $oRequestA4; // alias used by the view

        return view('requests.show', compact('oRequest', 'aAvailableActions', 'aStatusHistory'));
    }

    /**
     * Show the form for editing the specified request.
     *
     * @param  int  $iId
     * @return \Illuminate\View\View
     */
    public function edit(int $iId): View
    {
        $oRequestA4 = RequestA4::findOrFail($iId);

        $this->authorize('update', $oRequestA4);

        $aRequestTypes = RequestType::orderBy('name')->get();
        $aPriorities   = Priority::where('is_active', true)->orderBy('sort_order')->get();
        $aCompanies    = Company::orderBy('name')->get();
        $aSoftwares    = Software::orderBy('name')->get();
        $aStatuses     = Status::orderBy('sort_order')->get();
        $aTeams        = Team::orderBy('name')->get();
        $oRequest      = $oRequestA4; // alias used by the view

        return view('requests.edit', compact('oRequest', 'aRequestTypes', 'aPriorities', 'aCompanies', 'aSoftwares', 'aStatuses', 'aTeams'));
    }

    /**
     * Update the specified request in storage.
     *
     * @param  \App\Http\Requests\UpdateRequestA4Request  $oRequest
     * @param  int                                         $iId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequestA4Request $oRequest, int $iId): RedirectResponse
    {
        $oRequestA4 = RequestA4::findOrFail($iId);

        $this->authorize('update', $oRequestA4);

        $aOldValues  = $oRequestA4->toArray();
        $aValidated  = $oRequest->validated();

        $aCompanyIds  = $aValidated['company_ids']  ?? [];
        $aSoftwareIds = $aValidated['software_ids'] ?? [];
        unset($aValidated['company_ids'], $aValidated['software_ids']);

        $oRequestA4->update($aValidated);
        $oRequestA4->companies()->sync($aCompanyIds);
        $oRequestA4->softwares()->sync($aSoftwareIds);

        // Handle new file attachments
        if ($oRequest->hasFile('attachments')) {
            foreach ($oRequest->file('attachments') as $oFile) {
                $sPath = $oFile->store('attachments/requests', 'private');
                $oRequestA4->attachments()->create([
                    'filename'    => $oFile->getClientOriginalName(),
                    'path'        => $sPath,
                    'mime_type'   => $oFile->getMimeType(),
                    'size'        => $oFile->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        $this->oActivityLogService->log(
            'updated',
            "Fiche A4 mise à jour : {$oRequestA4->reference}",
            $oRequestA4,
            $aOldValues,
            $oRequestA4->fresh()->toArray()
        );

        return redirect()
            ->route('requests.show', $oRequestA4->id)
            ->with('success', __('messages.request_updated'));
    }

    /**
     * Soft-delete the specified request.
     *
     * @param  int  $iId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $iId): RedirectResponse
    {
        $oRequestA4 = RequestA4::findOrFail($iId);

        $this->authorize('delete', $oRequestA4);

        $this->oActivityLogService->log(
            'deleted',
            "Fiche A4 supprimée : {$oRequestA4->reference} - {$oRequestA4->title}",
            $oRequestA4
        );

        $oRequestA4->delete();

        return redirect()
            ->route('requests.index')
            ->with('success', __('messages.request_deleted'));
    }

    /**
     * Generate and download the PDF export of the specified request.
     *
     * @param  int  $iId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pdf(int $iId): \Symfony\Component\HttpFoundation\Response
    {
        $oRequestA4 = RequestA4::with([
            'status', 'priority', 'requestType',
            'requester', 'companies', 'softwares',
            'tasks', 'attachments',
        ])->findOrFail($iId);

        $this->authorize('exportPdf', $oRequestA4);

        $oAttachment = $this->oPdfService->generateRequestPdf($oRequestA4);

        $this->oActivityLogService->log(
            'pdf_exported',
            "PDF exporté pour la fiche : {$oRequestA4->reference} (v{$oAttachment->pdf_version_number})",
            $oRequestA4
        );

        return response()->download(
            storage_path("app/private/{$oAttachment->path}"),
            $oAttachment->original_name,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Execute a workflow action (status transition) on the specified request.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @param  int                       $iId
     * @param  int                       $iActionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function executeAction(Request $oHttpRequest, int $iId, int $iActionId): RedirectResponse
    {
        $oRequestA4 = RequestA4::findOrFail($iId);
        $oAction    = StatusAction::findOrFail($iActionId);

        $this->authorize('executeAction', [$oRequestA4, $oAction]);

        $oHttpRequest->validate([
            'comment' => $oAction->requires_comment
                ? 'required|string|max:1000'
                : 'nullable|string|max:1000',
        ]);

        $sComment = $oHttpRequest->input('comment', '');

        /** @var \App\Models\User $oUser */
        $oUser = Auth::user();

        $this->oWorkflowService->executeTransition($oRequestA4, $oAction, $oUser, $sComment);

        return redirect()
            ->route('requests.show', $oRequestA4->id)
            ->with('success', __('messages.action_executed', ['action' => $oAction->action_label]));
    }
}
