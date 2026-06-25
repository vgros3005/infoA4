@extends('layouts.app')

@section('title', __('Reporting — Temps'))
@section('page-title', __('Suivi du temps'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reporting') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Temps') }}</li>
@endsection

@section('content')
{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('reports.time') }}">
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
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">{{ __('Utilisateur') }}</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach($oUsers as $oU)
                            <option value="{{ $oU->id }}" {{ request('user_id') == $oU->id ? 'selected' : '' }}>
                                {{ $oU->full_name }}
                            </option>
                        @endforeach
                    </select>
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

{{-- Heures par utilisateur --}}
<div class="row g-3 mb-4">
    @foreach($aHoursByUser as $oHoursRow)
        <div class="col-12 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fw-semibold small text-muted mb-1">
                        {{ $oHoursRow->user->full_name ?? __('Inconnu') }}
                    </div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($oHoursRow->total_hours, 1) }}h</div>
                </div>
            </div>
        </div>
    @endforeach
    @if($aHoursByUser->isEmpty())
        <div class="col-12">
            <div class="alert alert-info mb-0">{{ __('Aucune saisie de temps sur cette période.') }}</div>
        </div>
    @endif
</div>

{{-- Tableau des saisies --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Détail des saisies') }}
        </h5>
        <span class="badge bg-secondary">{{ $oEntries->total() }} {{ __('entrées') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Date') }}</th>
                        <th>{{ __('Utilisateur') }}</th>
                        <th>{{ __('Tâche') }}</th>
                        <th>{{ __('Fiche') }}</th>
                        <th class="text-end">{{ __('Heures') }}</th>
                        <th>{{ __('Commentaire') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oEntries as $oEntry)
                        <tr>
                            <td class="ps-3 small">{{ $oEntry->entry_date?->format('d/m/Y') }}</td>
                            <td class="small">{{ $oEntry->user->full_name ?? '—' }}</td>
                            <td class="small">
                                @if($oEntry->task)
                                    <a href="{{ route('tasks.show', $oEntry->task_id) }}" class="text-decoration-none">
                                        {{ Str::limit($oEntry->task->title, 40) }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small">
                                @if($oEntry->task?->requestA4)
                                    <a href="{{ route('requests.show', $oEntry->task->request_a4_id) }}" class="text-decoration-none text-muted">
                                        {{ $oEntry->task->requestA4->reference }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end fw-medium">{{ number_format($oEntry->hours, 1) }}h</td>
                            <td class="small text-muted">{{ Str::limit($oEntry->comment ?? '', 50) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ __('Aucune saisie de temps sur cette période.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($oEntries->hasPages())
        <div class="card-footer bg-transparent">
            {{ $oEntries->links() }}
        </div>
    @endif
</div>
@endsection
