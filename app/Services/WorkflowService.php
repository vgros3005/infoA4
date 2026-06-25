<?php

namespace App\Services;

use App\Models\RequestA4;
use App\Models\StatusAction;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
        private readonly PdfService         $oPdfService,
    ) {
    }

    /**
     * Execute a workflow status transition on a request A4.
     *
     * Steps:
     * 1. Verify the transition is authorized (action belongs to current status + roles).
     * 2. Update the request's status.
     * 3. Create a StatusHistory record.
     * 4. If the new status freezes the request, set is_frozen = true.
     * 5. If the new status generates a PDF, trigger PDF generation.
     * 6. Log the activity.
     *
     * @param  \App\Models\RequestA4    $oRequest   The request to transition.
     * @param  \App\Models\StatusAction $oAction    The action being executed.
     * @param  \App\Models\User         $oUser      The user performing the action.
     * @param  string                   $sComment   Optional comment for the history record.
     * @return void
     *
     * @throws \RuntimeException  If the transition is not valid.
     */
    public function executeTransition(
        RequestA4    $oRequest,
        StatusAction $oAction,
        User         $oUser,
        string       $sComment = ''
    ): void {
        // --- Guard: action must belong to the current status ---
        if ((int) $oAction->status_id !== (int) $oRequest->status_id) {
            throw new \RuntimeException(
                "Action #{$oAction->id} does not belong to current status #{$oRequest->status_id}."
            );
        }

        // --- Guard: action must be active ---
        if (!$oAction->is_active) {
            throw new \RuntimeException("Action #{$oAction->id} is not active.");
        }

        // --- Guard: user must have an allowed role (if any are defined) ---
        $oRoles = $oAction->roles()->get();
        if ($oRoles->isNotEmpty()) {
            $bAuthorized = $oRoles->contains(fn($oRole) => $oUser->hasRole($oRole->name));
            if (!$bAuthorized) {
                throw new \RuntimeException(
                    "User #{$oUser->id} does not have the required role to execute action #{$oAction->id}."
                );
            }
        }

        DB::transaction(function () use ($oRequest, $oAction, $oUser, $sComment) {
            $iOldStatusId = $oRequest->status_id;
            $iNewStatusId = $oAction->target_status_id;

            // 1. Update the request status
            $oRequest->status_id = $iNewStatusId;

            // 2. Load the target status to check flags
            $oTargetStatus = $oAction->targetStatus()->firstOrFail();

            // 3. Freeze the request if required by the new status
            if ($oTargetStatus->freezes_request) {
                $oRequest->is_frozen = true;
            }

            $oRequest->save();

            // 4. Record the status history
            StatusHistory::create([
                'request_a4_id'  => $oRequest->id,
                'from_status_id' => $iOldStatusId,
                'to_status_id'   => $iNewStatusId,
                'user_id'        => $oUser->id,
                'action'         => $oAction->action_label,
                'comment'        => $sComment,
            ]);

            // 5. Generate PDF if the new status requires it
            if ($oTargetStatus->generates_pdf) {
                try {
                    $this->oPdfService->generateRequestPdf($oRequest->fresh());
                } catch (\Throwable $oException) {
                    // Log the error but don't roll back the transition
                    Log::error("PDF generation failed for request #{$oRequest->id}: " . $oException->getMessage());
                }
            }

            // 6. Log the activity
            $this->oActivityLogService->log(
                'status_changed',
                "Statut changé de #{$iOldStatusId} vers #{$iNewStatusId} via l'action \"{$oAction->action_label}\"",
                $oRequest,
                ['status_id' => $iOldStatusId],
                ['status_id' => $iNewStatusId, 'is_frozen' => $oRequest->is_frozen]
            );
        });
    }
}
