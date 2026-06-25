<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAdminController extends Controller
{
    /**
     * Display a paginated, filterable list of activity logs.
     *
     * @param  \Illuminate\Http\Request  $oHttpRequest
     * @return \Illuminate\View\View
     */
    public function index(Request $oHttpRequest): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $oQuery = ActivityLog::with('user')->orderByDesc('created_at');

        // Filter by user
        if ($iUserId = $oHttpRequest->input('user_id')) {
            $oQuery->where('user_id', $iUserId);
        }

        // Filter by action
        if ($sAction = $oHttpRequest->input('action')) {
            $oQuery->where('action', $sAction);
        }

        // Filter by model type
        if ($sModelType = $oHttpRequest->input('loggable_type')) {
            $oQuery->where('loggable_type', $sModelType);
        }

        // Date range
        if ($sDateFrom = $oHttpRequest->input('date_from')) {
            $oQuery->where('created_at', '>=', $sDateFrom . ' 00:00:00');
        }
        if ($sDateTo = $oHttpRequest->input('date_to')) {
            $oQuery->where('created_at', '<=', $sDateTo . ' 23:59:59');
        }

        $oLogs  = $oQuery->paginate(50)->withQueryString();
        $oUsers = User::orderBy('name')->get(['id', 'name', 'first_name', 'last_name']);

        // Distinct actions for the filter dropdown
        $aActions = ActivityLog::distinct()->orderBy('action')->pluck('action');

        return view('admin.logs.index', compact('oLogs', 'oUsers', 'aActions'));
    }
}
