<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\JsonResponse;

class PriorityApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/priorities/{id}/requires-justification",
     *     operationId="apiPriorityRequiresJustification",
     *     summary="Check whether the given priority requires a textual justification",
     *     tags={"Priorities"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Priority ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Justification requirement flag",
     *         @OA\JsonContent(
     *             @OA\Property(property="requires_justification", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Priority not found")
     * )
     *
     * @param  int  $iId
     * @return \Illuminate\Http\JsonResponse
     */
    public function requiresJustification(int $iId): JsonResponse
    {
        $oPriority = Priority::findOrFail($iId);

        return response()->json([
            'requires_justification' => (bool) $oPriority->requires_justification,
        ]);
    }
}
