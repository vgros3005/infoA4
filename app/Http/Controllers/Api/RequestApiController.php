<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequestA4;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/requests",
     *     operationId="apiRequestIndex",
     *     summary="List requests A4 with optional filters",
     *     tags={"Requests"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Full-text search on title, reference or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status_id",
     *         in="query",
     *         description="Filter by status ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="request_type_id",
     *         in="query",
     *         description="Filter by request type ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority_id",
     *         in="query",
     *         description="Filter by priority ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter requests from this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter requests up to this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page (default 20, max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $oHttpRequest): JsonResponse
    {
        $this->authorize('viewAny', RequestA4::class);

        $oQuery = RequestA4::with(['status', 'priority', 'requestType', 'requester'])
            ->whereNull('deleted_at');

        if ($sSearch = $oHttpRequest->input('search')) {
            $oQuery->where(function ($q) use ($sSearch) {
                $q->where('title', 'like', "%{$sSearch}%")
                  ->orWhere('reference', 'like', "%{$sSearch}%")
                  ->orWhere('description', 'like', "%{$sSearch}%");
            });
        }

        if ($iStatusId = $oHttpRequest->input('status_id')) {
            $oQuery->where('status_id', $iStatusId);
        }

        if ($iTypeId = $oHttpRequest->input('request_type_id')) {
            $oQuery->where('request_type_id', $iTypeId);
        }

        if ($iPriorityId = $oHttpRequest->input('priority_id')) {
            $oQuery->where('priority_id', $iPriorityId);
        }

        if ($sDateFrom = $oHttpRequest->input('date_from')) {
            $oQuery->where('requested_date', '>=', $sDateFrom);
        }

        if ($sDateTo = $oHttpRequest->input('date_to')) {
            $oQuery->where('requested_date', '<=', $sDateTo);
        }

        $iPerPage = min((int) $oHttpRequest->input('per_page', 20), 100);

        $oRequests = $oQuery->orderByDesc('created_at')->paginate($iPerPage);

        return response()->json($oRequests);
    }

    /**
     * @OA\Get(
     *     path="/api/requests/{id}",
     *     operationId="apiRequestShow",
     *     summary="Get a single request A4 by ID",
     *     tags={"Requests"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Request A4 ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Request detail",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not found")
     * )
     *
     * @param  int  $iId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $iId): JsonResponse
    {
        $oRequestA4 = RequestA4::with([
            'status.actions',
            'priority',
            'requestType',
            'requester',
            'assignedTeam',
            'companies',
            'softwares',
            'tasks',
        ])->findOrFail($iId);

        $this->authorize('view', $oRequestA4);

        return response()->json($oRequestA4);
    }
}
