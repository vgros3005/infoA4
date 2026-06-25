@extends('layouts.app')

@section('title', __('Tâche') . ' — ' . Str::limit($oTask->title, 40))
@section('page-title', Str::limit($oTask->title, 60))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">{{ __('Tâches') }}</a></li>
    @if($oTask->requestA4)
        <li class="breadcrumb-item">
            <a href="{{ route('requests.show', $oTask->requestA4) }}">{{ $oTask->requestA4->reference }}</a>
        </li>
    @endif
    <li class="breadcrumb-item active">{{ Str::limit($oTask->title, 30) }}</li>
@endsection

@section('content')
<div class="row g-3">
    {{-- Colonne principale --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-check2-square me-2 text-primary"></i>{{ __('Détails de la tâche') }}
                </h5>
                <div class="d-flex gap-2">
                    @can('update', $oTask)
                        <a href="{{ route('tasks.edit', $oTask) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}
                        </a>
                    @endcan
                    <a href="{{ route('time-entries.index', ['task_id' => $oTask->id]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-clock me-1"></i>{{ __('Saisir du temps') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">{{ __('Type') }}</p>
                        <p class="fw-medium mb-0">{{ $oTask->taskType->name ?? '—' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">{{ __('Statut') }}</p>
                        @php $sBadge = match($oTask->status) { 'done' => 'success', 'in_progress' => 'primary', 'cancelled' => 'danger', default => 'secondary' }; @endphp
                        <span class="badge bg-{{ $sBadge }}">{{ __('task_status.' . $oTask->status) }}</span>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">{{ __('Assigné à') }}</p>
                        <p class="fw-medium mb-0">{{ $oTask->assignedUser->full_name ?? '—' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">{{ __('Fiche A4') }}</p>
                        @if($oTask->requestA4)
                            <a href="{{ route('requests.show', $oTask->requestA4) }}" class="fw-medium">
                                {{ $oTask->requestA4->reference }}
                            </a>
                        @else
                            <p class="mb-0 text-muted">{{ __('Tâche indépendante') }}</p>
                        @endif
                    </div>
                    <div class="col-sm-3">
                        <p class="text-muted small mb-1">{{ __('Début') }}</p>
                        <p class="fw-medium mb-0">{{ $oTask->start_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div class="col-sm-3">
                        <p class="text-muted small mb-1">{{ __('Fin prévue') }}</p>
                        <p class="fw-medium mb-0 {{ $oTask->end_date?->isPast() && $oTask->status !== 'done' ? 'text-danger fw-bold' : '' }}">
                            {{ $oTask->end_date?->format('d/m/Y') ?? '—' }}
                        </p>
                    </div>
                    <div class="col-sm-3">
                        <p class="text-muted small mb-1">{{ __('Heures estimées') }}</p>
                        <p class="fw-medium mb-0">{{ $oTask->estimated_hours ?? '—' }}h</p>
                    </div>
                    <div class="col-sm-3">
                        <p class="text-muted small mb-1">{{ __('Avancement') }}</p>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $oTask->progress ?? 0 }}%"></div>
                        </div>
                        <small>{{ $oTask->progress ?? 0 }}%</small>
                    </div>
                </div>

                @if($oTask->description)
                    <hr>
                    <p class="text-muted small mb-1">{{ __('Description') }}</p>
                    <div class="border rounded p-3 bg-light">{!! $oTask->description !!}</div>
                @endif
            </div>
        </div>

        {{-- Temps reporté --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2 text-secondary"></i>{{ __('Temps reporté') }}
                </h5>
                <span class="badge bg-primary">
                    {{ $oTask->timeEntries->sum('hours') }}h {{ __('total') }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Utilisateur') }}</th>
                                <th>{{ __('Heures') }}</th>
                                <th>{{ __('Commentaire') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($oTask->timeEntries as $oEntry)
                                <tr>
                                    <td><small>{{ $oEntry->entry_date->format('d/m/Y') }}</small></td>
                                    <td><small>{{ $oEntry->user->full_name ?? '—' }}</small></td>
                                    <td><span class="badge bg-light text-dark border">{{ $oEntry->hours }}h</span></td>
                                    <td><small class="text-muted">{{ Str::limit($oEntry->comment ?? '', 60) }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">{{ __('Aucun temps reporté') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Colonne latérale --}}
    <div class="col-12 col-lg-4">
        {{-- Dépendances --}}
        @if($oTask->dependencies->isNotEmpty() || $oTask->dependents->isNotEmpty())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-diagram-2 me-2 text-warning"></i>{{ __('Dépendances') }}
                </h6>
            </div>
            <div class="card-body py-2">
                @if($oTask->dependencies->isNotEmpty())
                    <p class="text-muted small mb-1">{{ __('Cette tâche dépend de :') }}</p>
                    @foreach($oTask->dependencies as $oDep)
                        <a href="{{ route('tasks.show', $oDep) }}" class="d-block small mb-1">
                            <i class="bi bi-arrow-right-short"></i>{{ Str::limit($oDep->title, 40) }}
                        </a>
                    @endforeach
                @endif
                @if($oTask->dependents->isNotEmpty())
                    <p class="text-muted small mb-1 mt-2">{{ __('Bloque :') }}</p>
                    @foreach($oTask->dependents as $oDep)
                        <a href="{{ route('tasks.show', $oDep) }}" class="d-block small mb-1">
                            <i class="bi bi-arrow-right-short"></i>{{ Str::limit($oDep->title, 40) }}
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
        @endif

        {{-- Pièces jointes --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h6 class="card-title mb-0">
                    <i class="bi bi-paperclip me-2"></i>{{ __('Pièces jointes') }}
                </h6>
            </div>
            <div class="card-body py-2">
                @forelse($oTask->attachments as $oAttachment)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-file-earmark text-muted"></i>
                        <a href="{{ route('attachments.download', $oAttachment) }}" class="small text-truncate">
                            {{ $oAttachment->original_name }}
                        </a>
                        <small class="text-muted ms-auto">{{ round($oAttachment->size / 1024) }} Ko</small>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('Aucune pièce jointe') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
