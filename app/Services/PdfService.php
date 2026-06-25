<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\RequestA4;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    /**
     * Generate a versioned PDF for the given RequestA4 and store it as an Attachment.
     *
     * The PDF filename follows the pattern:
     *   <reference>_v<version>_<YYYYMMDD>.pdf
     *
     * The PDF version number is incremented from the last existing PDF attachment.
     *
     * @param  \App\Models\RequestA4  $oRequest
     * @return \App\Models\Attachment  The stored attachment record.
     */
    public function generateRequestPdf(RequestA4 $oRequest): Attachment
    {
        // Load all required relations if not already loaded
        $oRequest->loadMissing([
            'status',
            'priority',
            'requestType',
            'requester',
            'assignedTeam',
            'companies',
            'softwares',
            'tasks.assignedUser',
            'tasks.taskType',
            'statusHistories.user',
            'statusHistories.fromStatus',
            'statusHistories.toStatus',
        ]);

        // Determine the next PDF version number
        $iLastVersion = (int) $oRequest->pdfVersions()->max('pdf_version_number');
        $iNewVersion  = $iLastVersion + 1;

        // Convert img URLs to base64 data URIs so DomPDF can render them
        $oRequest->content = $this->embedImagesAsDataUris($oRequest->content ?? '');

        // Render the Blade view to a PDF
        $oPdf = Pdf::loadView('requests.pdf', [
            'oRequest'    => $oRequest,
            'iVersion'    => $iNewVersion,
            'sGeneratedAt' => now()->format('d/m/Y H:i'),
        ]);

        $oPdf->setPaper('A4', 'portrait');

        // Build the filename and storage path
        $sFilename    = sprintf(
            '%s_v%d_%s.pdf',
            $oRequest->reference,
            $iNewVersion,
            now()->format('Ymd')
        );
        $sStoragePath = "pdf/requests/{$oRequest->id}/{$sFilename}";

        // Save the PDF to private disk
        Storage::disk('private')->put($sStoragePath, $oPdf->output());

        // Create the Attachment record (description stores status name at generation time)
        $oAttachment = $oRequest->attachments()->create([
            'original_name'      => $sFilename,
            'stored_name'        => $sFilename,
            'disk'               => 'private',
            'path'               => $sStoragePath,
            'mime_type'          => 'application/pdf',
            'file_size'          => Storage::disk('private')->size($sStoragePath),
            'is_pdf_version'     => true,
            'pdf_version_number' => $iNewVersion,
            'uploaded_by'        => Auth::id(),
            'description'        => $oRequest->status->name ?? '—',
        ]);

        // Also update the pdf_version column on the request
        $oRequest->pdf_version = $iNewVersion;
        $oRequest->saveQuietly();

        return $oAttachment;
    }

    /**
     * Replace <img src="http://..."> with inline base64 data URIs so that
     * DomPDF (which has enable_remote=false) can render embedded images.
     *
     * Only processes URLs that resolve to local files on this server.
     */
    private function embedImagesAsDataUris(string $sHtml): string
    {
        if (empty($sHtml)) {
            return $sHtml;
        }

        $sAppUrl    = rtrim(config('app.url'), '/');
        $sPublicPath = public_path();

        return preg_replace_callback(
            '/<img([^>]*)\bsrc=["\']([^"\']+)["\']([^>]*)>/i',
            function (array $aMatches) use ($sAppUrl, $sPublicPath): string {
                $sSrc    = $aMatches[2];
                $sBefore = $aMatches[1];
                $sAfter  = $aMatches[3];

                // Already a data URI — keep as-is
                if (str_starts_with($sSrc, 'data:')) {
                    return $aMatches[0];
                }

                // Resolve URL to an absolute local path
                $sFilePath = null;

                if (str_starts_with($sSrc, $sAppUrl)) {
                    $sRelative = urldecode(substr($sSrc, strlen($sAppUrl)));
                    $sFilePath = $sPublicPath . str_replace('/', DIRECTORY_SEPARATOR, $sRelative);
                } elseif (str_starts_with($sSrc, '/')) {
                    $sFilePath = $sPublicPath . str_replace('/', DIRECTORY_SEPARATOR, urldecode($sSrc));
                }

                if ($sFilePath === null) {
                    return $aMatches[0];
                }

                // Resolve symlinks (Windows: public/storage → storage/app/public)
                $sRealPath = realpath($sFilePath);
                if ($sRealPath === false || !is_file($sRealPath)) {
                    return $aMatches[0];
                }
                $sFilePath = $sRealPath;

                $sMime     = mime_content_type($sFilePath) ?: 'image/png';
                $sBase64   = base64_encode(file_get_contents($sFilePath));
                $sDataUri  = "data:{$sMime};base64,{$sBase64}";

                return "<img{$sBefore}src=\"{$sDataUri}\"{$sAfter}>";
            },
            $sHtml
        );
    }
}
