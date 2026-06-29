<?php

namespace App\Http\Controllers;

use App\Models\RequestA4;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the main reporting dashboard (requests by status, overview metrics).
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function index(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', RequestA4::class);

        $sDateFrom = $oHttpRequest->input('date_from', now()->startOfYear()->toDateString());
        $sDateTo   = $oHttpRequest->input('date_to',   now()->toDateString());

        // --- Répartition par statut (requests_count = alias attendu par la vue) ---
        $aByStatus = Status::withCount([
                'requestsA4 as requests_count' => fn($q) => $q->whereNull('deleted_at'),
            ])
            ->orderBy('sort_order')
            ->get();

        $iTotalRequests = RequestA4::whereNull('deleted_at')->count();

        // --- Fiches en retard (date souhaitée dépassée, statut non final) ---
        $aOverdue = RequestA4::with(['status', 'priority', 'requester'])
            ->whereNull('deleted_at')
            ->whereNotNull('desired_date')
            ->whereDate('desired_date', '<', now()->toDateString())
            ->whereHas('status', fn($q) => $q->where('is_final', false))
            ->orderBy('desired_date')
            ->limit(15)
            ->get();

        // --- Charge par développeur : tâches actives + heures ---
        $oActiveTasks = Task::with('assignedUser')
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('assigned_to')
            ->get();

        $aLoadByDeveloper = $oActiveTasks
            ->groupBy('assigned_to')
            ->map(function ($oUserTasks) {
                $oUser = $oUserTasks->first()->assignedUser;
                if (!$oUser) {
                    return null;
                }
                $oUser->active_tasks      = $oUserTasks->count();
                $oUser->estimated_hours   = (float) $oUserTasks->sum('estimated_hours');
                $oUser->total_hours       = (float) $oUserTasks->sum('actual_hours');
                return $oUser;
            })
            ->filter()
            ->sortByDesc('active_tasks')
            ->values();

        // --- Récapitulatif des demandes sur la période ---
        $aRequestsSummary = RequestA4::with(['status', 'priority'])
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', $sDateFrom)
            ->whereDate('created_at', '<=', $sDateTo)
            ->orderByDesc('created_at')
            ->get();

        $aStats = ['total' => $iTotalRequests];

        return view('reports.index', compact(
            'aByStatus', 'aStats', 'aOverdue', 'aLoadByDeveloper',
            'aRequestsSummary', 'sDateFrom', 'sDateTo'
        ));
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
     * Time summary: yesterday, current week (per team/person), and by request/activity type.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function timeSummary(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', RequestA4::class);

        // --- "Hier" = vendredi si on est lundi ---
        $oToday = Carbon::today();
        if ($oToday->dayOfWeek === Carbon::MONDAY) {
            $dYesterday      = $oToday->copy()->previous(Carbon::FRIDAY);
            $sYesterdayLabel = __('Vendredi') . ' ' . $dYesterday->translatedFormat('d F Y');
        } else {
            $dYesterday      = $oToday->copy()->subDay();
            $sYesterdayLabel = __('Hier') . ' (' . $dYesterday->translatedFormat('l d F Y') . ')';
        }

        $dWeekStart = $oToday->copy()->startOfWeek(Carbon::MONDAY);
        $dWeekEnd   = $oToday->copy();

        // --- Fonction groupement équipe > personne ---
        $fnGroupByTeamUser = function ($oEntries) {
            // Group entries by team name (first team alphabetically), then by user
            $aGrouped = [];
            foreach ($oEntries as $oEntry) {
                $oUser      = $oEntry->user;
                $oFirstTeam = $oUser?->teams->sortBy('name')->first();
                $sTeamKey   = $oFirstTeam?->name ?? __('Sans équipe');
                $iTeamId    = $oFirstTeam?->id   ?? 0;
                $iUserId    = $oUser?->id          ?? 0;

                if (!isset($aGrouped[$sTeamKey])) {
                    $aGrouped[$sTeamKey] = [
                        'team_id'    => $iTeamId,
                        'team_name'  => $sTeamKey,
                        'team_total' => 0.0,
                        'users'      => [],
                    ];
                }
                if (!isset($aGrouped[$sTeamKey]['users'][$iUserId])) {
                    $aGrouped[$sTeamKey]['users'][$iUserId] = [
                        'user'        => $oUser,
                        'total_hours' => 0.0,
                    ];
                }
                $aGrouped[$sTeamKey]['users'][$iUserId]['total_hours'] += (float) $oEntry->hours;
                $aGrouped[$sTeamKey]['team_total']                      += (float) $oEntry->hours;
            }

            // Sort: teams alphabetically, users by descending hours
            ksort($aGrouped);
            foreach ($aGrouped as &$aTeam) {
                uasort($aTeam['users'], fn($a, $b) => $b['total_hours'] <=> $a['total_hours']);
                $aTeam['users'] = array_values($aTeam['users']);
            }
            unset($aTeam);

            return $aGrouped;
        };

        // --- Saisies d'hier ---
        $aYesterdayRaw  = TaskTimeEntry::with(['user.teams', 'task.taskType', 'task.requestA4'])
            ->whereDate('entry_date', $dYesterday)
            ->orderBy('user_id')
            ->get();
        $nYesterdayTotal    = (float) $aYesterdayRaw->sum('hours');
        $aYesterdayByTeam   = $fnGroupByTeamUser($aYesterdayRaw);

        // --- Saisies de la semaine en cours ---
        $aWeekRaw = TaskTimeEntry::with(['user.teams', 'task.taskType', 'task.requestA4'])
            ->whereBetween('entry_date', [$dWeekStart->toDateString(), $dWeekEnd->toDateString()])
            ->orderBy('user_id')
            ->get();
        $nWeekTotal    = (float) $aWeekRaw->sum('hours');
        $aWeekByTeam   = $fnGroupByTeamUser($aWeekRaw);

        // --- Par demande + type de tâche (semaine) ---
        $aByRequest = $aWeekRaw
            ->filter(fn(TaskTimeEntry $oEntry) => !is_null($oEntry->task?->requestA4))
            ->groupBy(fn(TaskTimeEntry $oEntry) => $oEntry->task->requestA4->id)
            ->map(function ($aRequestEntries) {
                $oRequest    = $aRequestEntries->first()->task->requestA4;

                $aByTaskType = $aRequestEntries
                    ->groupBy(fn(TaskTimeEntry $oEntry) => $oEntry->task?->task_type_id ?? 0)
                    ->map(function ($aTypeEntries) {
                        $oType = $aTypeEntries->first()->task?->taskType;
                        return [
                            'task_type'   => $oType,
                            'total_hours' => (float) $aTypeEntries->sum('hours'),
                        ];
                    })
                    ->sortByDesc('total_hours')
                    ->values();

                return [
                    'request'      => $oRequest,
                    'total_hours'  => (float) $aRequestEntries->sum('hours'),
                    'by_task_type' => $aByTaskType,
                ];
            })
            ->sortByDesc('total_hours')
            ->values();

        // Entrées de la semaine sans demande associée
        $nWeekNoRequestHours = (float) $aWeekRaw
            ->filter(fn(TaskTimeEntry $oEntry) => is_null($oEntry->task?->requestA4))
            ->sum('hours');

        return view('reports.time_summary', compact(
            'aYesterdayByTeam', 'nYesterdayTotal', 'sYesterdayLabel', 'dYesterday',
            'aWeekByTeam',      'nWeekTotal',       'dWeekStart',      'dWeekEnd',
            'aByRequest',       'nWeekNoRequestHours'
        ));
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
