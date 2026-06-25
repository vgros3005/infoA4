<?php

namespace App\Http\Controllers;

use App\Models\RequestA4;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the main reporting dashboard (requests by status, overview metrics).
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $this->authorize('viewAny', RequestA4::class);

        // Requests grouped by status
        $aRequestsByStatus = Status::withCount(['requestsA4' => fn($q) => $q->whereNull('deleted_at')])
            ->orderBy('sort_order')
            ->get();

        // Requests opened vs. closed per month (last 12 months)
        $aMonthlyStats = RequestA4::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        return view('reports.index', compact('aRequestsByStatus', 'aMonthlyStats'));
    }

    /**
     * Time reporting: hours logged per user/task/period.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function time(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', RequestA4::class);

        $sDateFrom = $oHttpRequest->input('date_from', now()->startOfMonth()->toDateString());
        $sDateTo   = $oHttpRequest->input('date_to',   now()->endOfMonth()->toDateString());
        $iUserId   = $oHttpRequest->input('user_id');

        $oQuery = TaskTimeEntry::with(['user', 'task.requestA4'])
            ->whereBetween('entry_date', [$sDateFrom, $sDateTo]);

        if ($iUserId) {
            $oQuery->where('user_id', $iUserId);
        }

        // Aggregated hours per user
        $aHoursByUser = TaskTimeEntry::selectRaw('user_id, SUM(hours) as total_hours')
            ->whereBetween('entry_date', [$sDateFrom, $sDateTo])
            ->groupBy('user_id')
            ->with('user:id,name,first_name,last_name')
            ->get();

        $oEntries = $oQuery->orderByDesc('entry_date')->paginate(50)->withQueryString();
        $oUsers   = User::where('is_active', true)->orderBy('name')->get();

        return view('reports.time', compact('oEntries', 'aHoursByUser', 'oUsers', 'sDateFrom', 'sDateTo'));
    }

    /**
     * Load report: team/developer workload visualization.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function load(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', RequestA4::class);

        $sDateFrom = $oHttpRequest->input('date_from', now()->startOfWeek()->toDateString());
        $sDateTo   = $oHttpRequest->input('date_to',   now()->addWeeks(4)->endOfWeek()->toDateString());

        // Active tasks in the period with their assignees
        $oTasks = Task::with(['assignedUser', 'requestA4'])
            ->whereNull('deleted_at')
            ->where(function ($q) use ($sDateFrom, $sDateTo) {
                $q->whereBetween('start_date', [$sDateFrom, $sDateTo])
                  ->orWhereBetween('end_date', [$sDateFrom, $sDateTo]);
            })
            ->orderBy('assigned_to')
            ->orderBy('start_date')
            ->get();

        // Group by user for the view
        $aTasksByUser = $oTasks->groupBy('assigned_to');

        $oUsers = User::where('is_active', true)->orderBy('name')->get();

        return view('reports.load', compact('aTasksByUser', 'oUsers', 'sDateFrom', 'sDateTo'));
    }

    /**
     * Export requests as CSV.
     */
    public function export(Request $oHttpRequest): Response
    {
        $this->authorize('viewAny', RequestA4::class);

        $oRequests = RequestA4::with(['status', 'priority', 'requester', 'requestType'])
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        $sCsv = implode(';', [
            'Référence', 'Titre', 'Type', 'Priorité', 'Statut', 'Demandeur', 'Date demande', 'Date souhaitée',
        ]) . "\n";

        foreach ($oRequests as $oReq) {
            $sCsv .= implode(';', [
                $oReq->reference,
                '"' . str_replace('"', '""', $oReq->title) . '"',
                $oReq->requestType->name ?? '',
                $oReq->priority->name ?? '',
                $oReq->status->name ?? '',
                $oReq->requester->full_name ?? '',
                $oReq->requested_date?->format('d/m/Y') ?? '',
                $oReq->desired_date?->format('d/m/Y') ?? '',
            ]) . "\n";
        }

        return response($sCsv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="fiches-a4-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
