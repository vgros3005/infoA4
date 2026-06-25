@extends('layouts.app')

@section('title', __('Reporting'))
@section('page-title', __('Tableau de bord reporting'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('Reporting') }}</li>
@endsection

@section('content')
{{-- Filtres de période --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">{{ __('Depuis') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">{{ __('Jusqu\'au') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="bi bi-graph-up me-1"></i>{{ __('Actualiser') }}
                    </button>
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <a href="{{ route('reports.export') }}?{{ request()->getQueryString() }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>{{ __('Exporter CSV') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Section 1 : Fiches par statut --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>{{ __('Répartition par statut') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($aByStatus ?? [] as $oStatus)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-medium small">
                                    <span class="badge me-1" style="background-color: var(--bs-{{ $oStatus->color ?? 'secondary' }}); font-size: 0.6rem;">&nbsp;</span>
                                    {{ $oStatus->translated_label }}
                                </span>
                                <span class="fw-bold">{{ $oStatus->requests_count }}</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                @php $nPct = $aStats['total'] > 0 ? round($oStatus->requests_count / $aStats['total'] * 100) : 0; @endphp
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $nPct }}%; background-color: var(--bs-{{ $oStatus->color ?? 'primary' }});"
                                     aria-valuenow="{{ $nPct }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">{{ $nPct }}% {{ __('du total') }}</small>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted text-center py-3">{{ __('Aucune donnée') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Fiches en retard --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i>{{ __('Fiches en retard') }}
                </h5>
                <span class="badge bg-danger">{{ count($aOverdue ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($aOverdue ?? [] as $oRequest)
                    <div class="d-flex align-items-center border-bottom px-3 py-2">
                        <div class="flex-grow-1">
                            <a href="{{ route('requests.show', $oRequest) }}" class="text-decoration-none">
                                <code class="text-primary small">{{ $oRequest->reference }}</code>
                                <span class="ms-2 small fw-medium">{{ Str::limit($oRequest->title, 35) }}</span>
                            </a>
                            <div>
                                <span class="badge" style="background-color: var(--bs-{{ $oRequest->status->color ?? 'secondary' }}); font-size: 0.65rem;">
                                    {{ $oRequest->status->translated_label ?? '—' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end ms-2">
                            <span class="badge bg-danger">
                                {{ $oRequest->desired_date->format('d/m/Y') }}
                            </span>
                            <small class="text-danger d-block">
                                +{{ $oRequest->desired_date->diffInDays(now()) }}j
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-success py-4">
                        <i class="bi bi-check-circle d-block fs-3 mb-2"></i>
                        {{ __('Aucune fiche en retard') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Charge par développeur --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people me-2 text-info"></i>{{ __('Charge par développeur') }}
                </h5>
            </div>
            <div class="card-body">
                @forelse($aLoadByDeveloper ?? [] as $oDev)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                     style="width: 28px; height: 28px; flex-shrink: 0;">
                                    <i class="bi bi-person text-primary small"></i>
                                </div>
                                <span class="fw-medium small">{{ $oDev->full_name }}</span>
                            </div>
                            <span class="small fw-bold">
                                {{ number_format($oDev->total_hours ?? 0, 1) }}h
                                <span class="text-muted fw-normal">/ {{ number_format($oDev->estimated_hours ?? 0, 1) }}h</span>
                            </span>
                        </div>
                        @php
                            $nEst = $oDev->estimated_hours ?? 0;
                            $nReal = $oDev->total_hours ?? 0;
                            $nPct = $nEst > 0 ? min(100, round($nReal / $nEst * 100)) : 0;
                            $sColor = $nPct > 100 ? 'bg-danger' : ($nPct > 80 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar {{ $sColor }}" role="progressbar"
                                 style="width: {{ $nPct }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">{{ $oDev->active_tasks ?? 0 }} {{ __('tâches actives') }}</small>
                            <small class="{{ $nPct > 100 ? 'text-danger' : 'text-muted' }}">{{ $nPct }}%</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4">{{ __('Aucune donnée de charge') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Tableau récapitulatif --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0">
        <h5 class="card-title mb-0">
            <i class="bi bi-table me-2 text-secondary"></i>{{ __('Récapitulatif des demandes') }}
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Référence') }}</th>
                        <th>{{ __('Titre') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Priorité') }}</th>
                        <th class="text-end">{{ __('Est. (h)') }}</th>
                        <th class="text-end">{{ __('Réel (h)') }}</th>
                        <th class="text-end">{{ __('Écart') }}</th>
                        <th>{{ __('Date souhaitée') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aRequestsSummary ?? [] as $oReq)
                        @php
                            $nEcart = ($oReq->actual_hours ?? 0) - ($oReq->estimated_hours ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('requests.show', $oReq) }}" class="text-decoration-none">
                                    <code class="text-primary small">{{ $oReq->reference }}</code>
                                </a>
                            </td>
                            <td><span class="small">{{ Str::limit($oReq->title, 45) }}</span></td>
                            <td>
                                <span class="badge" style="background-color: var(--bs-{{ $oReq->status->color ?? 'secondary' }})">
                                    {{ $oReq->status->translated_label ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: var(--bs-{{ $oReq->priority->color ?? 'secondary' }})">
                                    {{ $oReq->priority->name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end small">{{ number_format($oReq->estimated_hours ?? 0, 1) }}</td>
                            <td class="text-end small">{{ number_format($oReq->actual_hours ?? 0, 1) }}</td>
                            <td class="text-end small">
                                <span class="{{ $nEcart > 0 ? 'text-danger' : ($nEcart < 0 ? 'text-success' : 'text-muted') }}">
                                    {{ $nEcart >= 0 ? '+' : '' }}{{ number_format($nEcart, 1) }}
                                </span>
                            </td>
                            <td>
                                @if($oReq->desired_date)
                                    <span class="{{ $oReq->desired_date->isPast() ? 'text-danger fw-bold' : 'text-muted' }} small">
                                        {{ $oReq->desired_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                {{ __('Aucune donnée pour la période sélectionnée') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
