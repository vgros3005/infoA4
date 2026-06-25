@extends('layouts.app')

@section('title', __('Reporting — Charge'))
@section('page-title', __('Charge par développeur'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reporting') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Charge') }}</li>
@endsection

@section('content')
{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('reports.load') }}">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">{{ __('Du') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $sDateFrom }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">{{ __('Au') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $sDateTo }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-sm btn-secondary w-100">
                        <i class="bi bi-funnel me-1"></i>{{ __('Filtrer') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Vue par développeur --}}
@if($aTasksByUser->isEmpty())
    <div class="alert alert-info">{{ __('Aucune tâche planifiée sur cette période.') }}</div>
@else
    @foreach($oUsers as $oUser)
        @php
            $aUserTasks = $aTasksByUser->get($oUser->id, collect());
        @endphp
        @if($aUserTasks->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person me-2 text-primary"></i>{{ $oUser->full_name }}
                        <span class="badge bg-primary ms-2">{{ $aUserTasks->count() }} {{ __('tâche(s)') }}</span>
                    </h5>
                    @php
                        $nEstimatedHours = $aUserTasks->sum('estimated_hours');
                        $nActualHours    = $aUserTasks->sum('actual_hours');
                    @endphp
                    <div class="small text-muted">
                        {{ __('Estimé') }}: <strong>{{ number_format($nEstimatedHours, 1) }}h</strong>
                        @if($nActualHours > 0)
                            — {{ __('Réel') }}: <strong>{{ number_format($nActualHours, 1) }}h</strong>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('Tâche') }}</th>
                                    <th>{{ __('Fiche') }}</th>
                                    <th>{{ __('Début') }}</th>
                                    <th>{{ __('Fin') }}</th>
                                    <th class="text-end">{{ __('Estimé') }}</th>
                                    <th>{{ __('Statut') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aUserTasks as $oTask)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('tasks.show', $oTask->id) }}" class="text-decoration-none fw-medium">
                                                {{ Str::limit($oTask->title, 45) }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($oTask->requestA4)
                                                <a href="{{ route('requests.show', $oTask->request_a4_id) }}" class="text-muted text-decoration-none">
                                                    {{ $oTask->requestA4->reference }}
                                                </a>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-secondary">{{ __('Indép.') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $oTask->start_date?->format('d/m') ?? '—' }}</td>
                                        <td>{{ $oTask->end_date?->format('d/m') ?? '—' }}</td>
                                        <td class="text-end">{{ $oTask->estimated_hours ? number_format($oTask->estimated_hours, 1) . 'h' : '—' }}</td>
                                        <td>
                                            @php $sStatus = $oTask->status ?? 'pending' @endphp
                                            <span class="badge
                                                @if($sStatus === 'done') bg-success
                                                @elseif($sStatus === 'in_progress') bg-primary
                                                @elseif($sStatus === 'blocked') bg-danger
                                                @else bg-secondary
                                                @endif">
                                                {{ __('task_status.' . $sStatus) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Tâches sans utilisateur assigné --}}
    @php $aUnassigned = $aTasksByUser->get(null, collect()); @endphp
    @if($aUnassigned->isNotEmpty())
        <div class="card border-0 shadow-sm border-warning mb-4">
            <div class="card-header bg-warning bg-opacity-10 border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-x me-2 text-warning"></i>{{ __('Non assignées') }}
                    <span class="badge bg-warning text-dark ms-2">{{ $aUnassigned->count() }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Tâche') }}</th>
                                <th>{{ __('Fiche') }}</th>
                                <th>{{ __('Début') }}</th>
                                <th>{{ __('Fin') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aUnassigned as $oTask)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('tasks.show', $oTask->id) }}" class="text-decoration-none fw-medium">
                                            {{ Str::limit($oTask->title, 55) }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($oTask->requestA4)
                                            <a href="{{ route('requests.show', $oTask->request_a4_id) }}" class="text-muted text-decoration-none">
                                                {{ $oTask->requestA4->reference }}
                                            </a>
                                        @else —
                                        @endif
                                    </td>
                                    <td>{{ $oTask->start_date?->format('d/m') ?? '—' }}</td>
                                    <td>{{ $oTask->end_date?->format('d/m') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endif
@endsection
