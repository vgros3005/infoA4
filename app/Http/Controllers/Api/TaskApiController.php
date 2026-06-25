<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks/gantt-data",
     *     operationId="apiTaskGanttData",
     *     summary="Return tasks formatted for Frappe Gantt chart",
     *     tags={"Tasks"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="request_a4_id",
     *         in="query",
     *         description="Filter tasks by request A4 ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter tasks by assigned user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Array of Frappe Gantt task objects",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id",           type="string",  description="Task ID as string"),
     *                 @OA\Property(property="name",         type="string",  description="Task title"),
     *                 @OA\Property(property="start",        type="string",  format="date", description="Start date YYYY-MM-DD"),
     *                 @OA\Property(property="end",          type="string",  format="date", description="End date YYYY-MM-DD"),
     *                 @OA\Property(property="progress",     type="number",  description="Completion percentage 0-100"),
     *                 @OA\Property(property="dependencies", type="string",  description="Comma-separated dependency IDs"),
     *                 @OA\Property(property="custom_class", type="string",  description="CSS class based on task status")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function ganttData(Request $oHttpRequest): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $oQuery = Task::with(['dependencies', 'taskType'])
            ->forGantt()
            ->whereNull('deleted_at');

        if ($iRequestId = $oHttpRequest->input('request_a4_id')) {
            $oQuery->where('request_a4_id', $iRequestId);
        }

        if ($iUserId = $oHttpRequest->input('user_id')) {
            $oQuery->where('assigned_to', $iUserId);
        }

        $oTasks = $oQuery->orderBy('start_date')->get();

        $aGanttData = $oTasks->map(function (Task $oTask) {
            $sDependencies = $oTask->dependencies
                ->pluck('id')
                ->map(fn($iId) => (string) $iId)
                ->implode(',');

            $sTypeSlug = $oTask->taskType?->name ?? 'other';

            return [
                'id'           => (string) $oTask->id,
                'name'         => $oTask->title,
                'start'        => $oTask->start_date?->toDateString() ?? now()->toDateString(),
                'end'          => $oTask->end_date?->toDateString()   ?? now()->addDay()->toDateString(),
                'progress'     => (float) ($oTask->progress ?? 0),
                'dependencies' => $sDependencies,
                'custom_class' => 'task-type-' . $sTypeSlug,
            ];
        });

        return response()->json($aGanttData);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/by-user/{userId}",
     *     operationId="apiTasksByUser",
     *     summary="Return all active tasks assigned to a specific user",
     *     tags={"Tasks"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks for the user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id",              type="integer"),
     *                 @OA\Property(property="title",           type="string"),
     *                 @OA\Property(property="status",          type="string"),
     *                 @OA\Property(property="start_date",      type="string", format="date"),
     *                 @OA\Property(property="end_date",        type="string", format="date"),
     *                 @OA\Property(property="estimated_hours", type="number"),
     *                 @OA\Property(property="actual_hours",    type="number"),
     *                 @OA\Property(property="progress",        type="number"),
     *                 @OA\Property(property="request_a4",      type="object", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found")
     * )
     *
     * @param  int  $iUserId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byUser(int $iUserId): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        // Ensure the user exists
        $oUser = User::findOrFail($iUserId);

        $oTasks = Task::with(['requestA4:id,reference,title', 'taskType:id,name'])
            ->where('assigned_to', $oUser->id)
            ->whereNull('deleted_at')
            ->orderBy('end_date')
            ->get([
                'id', 'request_a4_id', 'task_type_id', 'title', 'status', 'priority',
                'start_date', 'end_date', 'estimated_hours', 'actual_hours', 'progress', 'is_recurring',
            ]);

        return response()->json($oTasks);
    }

    /**
     * Update start and end dates of a task (called from Gantt drag).
     */
    public function updateDates(Request $oHttpRequest, int $iId): JsonResponse
    {
        $oTask = Task::findOrFail($iId);
        $this->authorize('update', $oTask);

        $aValidated = $oHttpRequest->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $oTask->update($aValidated);

        return response()->json(['ok' => true]);
    }

    /**
     * Update the progress percentage of a task (called from Gantt progress bar).
     */
    public function updateProgress(Request $oHttpRequest, int $iId): JsonResponse
    {
        $oTask = Task::findOrFail($iId);
        $this->authorize('update', $oTask);

        $aValidated = $oHttpRequest->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $oTask->update($aValidated);

        return response()->json(['ok' => true]);
    }
}
