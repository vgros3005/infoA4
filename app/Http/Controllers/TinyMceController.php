<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TinyMceController extends Controller
{
    /**
     * Handle image upload from TinyMCE (button upload or clipboard paste).
     * Blobs pasted from clipboard may arrive without a proper extension;
     * we detect the MIME type and assign the correct extension ourselves.
     */
    public function upload(Request $oRequest): JsonResponse
    {
        $oRequest->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,bmp',
        ]);

        $oFile     = $oRequest->file('file');
        $sMime     = $oFile->getMimeType() ?? 'image/png';
        $sExtMap   = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp'  => 'bmp',
        ];
        $sExt      = $sExtMap[$sMime] ?? $oFile->getClientOriginalExtension() ?: 'png';
        $sFilename = Str::uuid() . '.' . $sExt;

        $sPath = $oFile->storeAs('tinymce-uploads', $sFilename, 'public');

        return response()->json(['location' => Storage::disk('public')->url($sPath)]);
    }
}
