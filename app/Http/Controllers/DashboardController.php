<?php

namespace App\Http\Controllers;

use App\Models\RequestA4;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard with key statistics and activity.
     */
    public function index(Request $oHttpRequest): View
    {
        /** @var \App\Models\User $oUser */
        $oUser = $oHttpRequest->user();

        // Statuts avec le nombre de fiches associées (deux requêtes simples pour éviter
        // les conflits entre withCount, SoftDeletes et les callbacks)
        $aByStatus = Status::orderBy('sort_order')->get();

        $aRequestCounts = RequestA4::whereNull('deleted_at')
            ->groupBy('status_id')
            ->selectRaw('status_id, COUNT(*) as requests_a4_count')
            ->pluck('requests_a4_count', 'status_id');

        $aByStatus->each(function (Status $oStatus) use ($aRequestCounts) {
            $oStatus->requests_a4_count = (int) ($aRequestCounts->get($oStatus->id, 0));
        });

        $iTotalRequests   = RequestA4::whereNull('deleted_at')->count();
        $iOpenRequests    = RequestA4::whereHas('status', fn($q) => $q->where('is_final', false))
            ->whereNull('deleted_at')->count();
        $iDoneRequests    = RequestA4::whereHas('status', fn($q) => $q->where('is_final', true))
            ->whereNull('deleted_at')->count();

        $sToday   = now()->toDateString();
        $aMyTasks = Task::with(['requestA4', 'taskType'])
            ->where('assigned_to', $oUser->id)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->where(function ($q) use ($sToday) {
                // Tâches actives aujourd'hui : plage contient aujourd'hui, ou sans dates
                $q->where(function ($q2) use ($sToday) {
                    $q2->whereDate('start_date', '<=', $sToday)
                       ->whereDate('end_date', '>=', $sToday);
                })->orWhereNull('start_date');
            })
            ->orderBy('end_date')
            ->limit(10)
            ->get();

        $sWeekStart  = now()->startOfWeek()->toDateString();
        $sWeekEnd    = now()->endOfWeek()->toDateString();
        $nWeeklyHours = (float) TaskTimeEntry::where('user_id', $oUser->id)
            ->whereBetween('entry_date', [$sWeekStart, $sWeekEnd])
            ->sum('hours');

        $aRecentRequests = RequestA4::with(['status', 'priority', 'requester', 'requestType'])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $aStats = [
            'total'       => $iTotalRequests,
            'in_progress' => $iOpenRequests,
            'completed'   => $iDoneRequests,
            'my_tasks'    => $aMyTasks->count(),
            'by_status'   => $aByStatus,
        ];

        return view('dashboard', compact('aStats', 'aMyTasks', 'aRecentRequests', 'nWeeklyHours'));
    }
}
