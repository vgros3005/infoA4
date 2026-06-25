@extends('layouts.app')

@section('title', __('Tableau de bord'))
@section('page-title', __('Tableau de bord'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('Tableau de bord') }}</li>
@endsection

@section('content')
{{-- Cartes statistiques --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                    </div>
                </div>
                <div>
                    <p class="text-muted small mb-0">{{ __('Total demandes') }}</p>
                    <h3 class="mb-0 fw-bold">{{ $aStats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    </div>
                </div>
                <div>
                    <p class="text-muted small mb-0">{{ __('En cours') }}</p>
                    <h3 class="mb-0 fw-bold">{{ $aStats['in_progress'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                    </div>
                </div>
                <div>
                    <p class="text-muted small mb-0">{{ __('Terminées') }}</p>
                    <h3 class="mb-0 fw-bold">{{ $aStats['completed'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-info bg-opacity-10 rounded-3 p-3">
                        <i class="bi bi-check2-square text-info fs-3"></i>
                    </div>
                </div>
                <div>
                    <p class="text-muted small mb-0">{{ __('Tâches assignées') }}</p>
                    <h3 class="mb-0 fw-bold">{{ $aStats['my_tasks'] ?? 0 }}</h3>
                    <small class="text-muted">{{ number_format($nWeeklyHours ?? 0, 1) }}h {{ __('cette semaine') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Graphique demandes par statut --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>{{ __('Demandes par statut') }}
                </h5>
            </div>
            <div class="card-body">
                @if($aStats['by_status']->isNotEmpty())
                    @php $nMax = $aStats['by_status']->max('requests_a4_count') ?: 1; @endphp
                    @foreach($aStats['by_status'] as $oStatus)
                        @php $nPct = round($oStatus->requests_a4_count / $nMax * 100); @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-medium">{{ $oStatus->translated_label }}</span>
                                <span class="small text-muted">{{ $oStatus->requests_a4_count }}</span>
                            </div>
                            <div class="progress" style="height: 18px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $nPct }}%; background-color: var(--bs-{{ $oStatus->color ?? 'primary' }});"
                                     aria-valuenow="{{ $nPct }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-4">{{ __('Aucune donnée disponible') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Mes tâches du jour --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-check me-2 text-success"></i>{{ __('Mes tâches du jour') }}
                </h5>
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('Voir tout') }}
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($aMyTasks as $oTask)
                    <div class="d-flex align-items-start border-bottom px-3 py-2">
                        <div class="flex-shrink-0 me-2 mt-1">
                            @if($oTask->progress >= 100)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-circle text-muted"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <p class="mb-0 text-truncate fw-medium small">{{ $oTask->title }}</p>
                            @if($oTask->requestA4)
                                <small class="text-muted">{{ $oTask->requestA4->reference }}</small>
                            @endif
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            <span class="badge bg-secondary">{{ $oTask->estimated_hours }}h</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4 px-3">
                        <i class="bi bi-emoji-smile d-block fs-3 mb-2"></i>
                        {{ __('Aucune tâche pour aujourd\'hui') }}
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Dernières demandes --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-clock-history me-2 text-secondary"></i>{{ __('5 dernières demandes') }}
        </h5>
        <a href="{{ route('requests.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-list-ul me-1"></i>{{ __('Toutes les demandes') }}
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Référence') }}</th>
                        <th>{{ __('Titre') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Priorité') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Demandeur') }}</th>
                        <th>{{ __('Date souhaitée') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aRecentRequests as $oRequest)
                        <tr>
                            <td><code class="text-primary">{{ $oRequest->reference }}</code></td>
                            <td class="fw-medium">{{ Str::limit($oRequest->title, 40) }}</td>
                            <td><span class="text-muted small">{{ $oRequest->requestType->name ?? '—' }}</span></td>
                            <td>
                                <span class="badge" style="background-color: var(--bs-{{ $oRequest->priority->color ?? 'secondary' }})">
                                    {{ $oRequest->priority->name ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: var(--bs-{{ $oRequest->status->color ?? 'secondary' }})">
                                    {{ $oRequest->status->translated_label ?? '—' }}
                                </span>
                            </td>
                            <td><span class="small">{{ $oRequest->requester->full_name ?? '—' }}</span></td>
                            <td>
                                @if($oRequest->desired_date)
                                    <span class="small {{ $oRequest->desired_date->isPast() && !in_array($oRequest->status->name, ['deployed', 'rejected']) ? 'text-danger fw-bold' : '' }}">
                                        {{ $oRequest->desired_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('requests.show', $oRequest) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                {{ __('Aucune demande récente') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
