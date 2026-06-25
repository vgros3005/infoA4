<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TinyMceController extends Controller
{
    public function upload(Request $oRequest): JsonResponse
    {
        $oRequest->validate(['file' => 'required|image|max:5120']);

        $sPath = $oRequest->file('file')->store('tinymce-uploads', 'public');

        return response()->json(['location' => Storage::disk('public')->url($sPath)]);
    }
}
