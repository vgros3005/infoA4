<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\RequestA4;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $oActivityLogService,
    ) {
    }

    /**
     * Upload an attachment for a given RequestA4.
     */
    public function storeForRequest(Request $oHttpRequest, int $iRequestId): RedirectResponse
    {
        $oRequestA4 = RequestA4::findOrFail($iRequestId);

        $this->authorize('update', $oRequestA4);

        $oHttpRequest->validate([
            'attachment' => ['required', 'file', 'max:20480'], // 20 MB
        ]);

        $oFile     = $oHttpRequest->file('attachment');
        $sPath     = $oFile->store('attachments/requests/' . $iRequestId, 'private');
        $sMimeType = $oFile->getMimeType();

        $oAttachment = $oRequestA4->attachments()->create([
            'original_name' => $oFile->getClientOriginalName(),
            'stored_name'   => basename($sPath),
            'path'          => $sPath,
            'mime_type'     => $sMimeType,
            'size'          => $oFile->getSize(),
            'uploaded_by'   => Auth::id(),
        ]);

        $this->oActivityLogService->log(
            'created',
            "Pièce jointe ajoutée : {$oAttachment->original_name}",
            $oRequestA4
        );

        return back()->with('success', __('messages.attachment_uploaded'));
    }

    /**
     * Download an attachment.
     */
    public function download(int $iId): StreamedResponse
    {
        $oAttachment = Attachment::findOrFail($iId);

        $this->authorize('view', $oAttachment);

        return Storage::disk('private')->download(
            $oAttachment->path,
            $oAttachment->original_name
        );
    }

    /**
     * Delete an attachment.
     */
    public function destroy(int $iId): RedirectResponse
    {
        $oAttachment = Attachment::findOrFail($iId);

        $this->authorize('delete', $oAttachment);

        Storage::disk('private')->delete($oAttachment->path);

        $this->oActivityLogService->log(
            'deleted',
            "Pièce jointe supprimée : {$oAttachment->original_name}",
            $oAttachment->attachable
        );

        $oAttachment->delete();

        return back()->with('success', __('messages.attachment_deleted'));
    }
}
