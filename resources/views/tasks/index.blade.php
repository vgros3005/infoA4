@extends('layouts.app')

@section('title', __('Tâches'))
@section('page-title', __('Liste des tâches'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('Tâches') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $oTasks->total() }} {{ __('tâche(s) trouvée(s)') }}</span>
    <div class="d-flex gap-2">
        <a href="{{ route('tasks.gantt') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-bar-chart-gantt me-1"></i>{{ __('Vue Gantt') }}
        </a>
        @can('create', \App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle tâche') }}
            </a>
        @endcan
    </div>
</div>

{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('tasks.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="{{ __('Titre…') }}" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Statut') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach($aStatuses as $sStatus)
                            <option value="{{ $sStatus }}" {{ request('status') == $sStatus ? 'selected' : '' }}>
                                {{ __('task_status.' . $sStatus) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">{{ __('Assigné à') }}</label>
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach($aUsers as $oUser)
                            <option value="{{ $oUser->id }}" {{ request('assigned_to') == $oUser->id ? 'selected' : '' }}>
                                {{ $oUser->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Période') }}</label>
                    <input type="month" name="period" class="form-control form-control-sm" value="{{ request('period') }}">
                </div>
                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-secondary flex-fill">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Réinitialiser') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Titre') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Fiche A4') }}</th>
                        <th>{{ __('Assigné à') }}</th>
                        <th>{{ __('Estimation') }}</th>
                        <th>{{ __('Avancement') }}</th>
                        <th>{{ __('Début') }}</th>
                        <th>{{ __('Fin') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th class="text-end pe-3">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oTasks as $oTask)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium small">{{ Str::limit($oTask->title, 40) }}</div>
                                @if($oTask->is_recurring)
                                    <span class="badge bg-info bg-opacity-25 text-info small">
                                        <i class="bi bi-arrow-repeat me-1"></i>{{ __('Récurrente') }}
                                    </span>
                                @endif
                            </td>
                            <td><span class="text-muted small">{{ $oTask->taskType->name ?? '—' }}</span></td>
                            <td>
                                @if($oTask->requestA4)
                                    <a href="{{ route('requests.show', $oTask->requestA4) }}" class="text-decoration-none">
                                        <code class="text-primary small">{{ $oTask->requestA4->reference }}</code>
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td><span class="small">{{ $oTask->assignee->full_name ?? '—' }}</span></td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $oTask->estimated_hours }}h</span>
                            </td>
                            <td style="min-width: 100px;">
                                <div class="progress mb-1" style="height: 5px;">
                                    <div class="progress-bar
                                        {{ $oTask->progress >= 100 ? 'bg-success' : ($oTask->progress >= 50 ? 'bg-primary' : 'bg-warning') }}"
                                        role="progressbar" style="width: {{ $oTask->progress }}%"></div>
                                </div>
                                <small class="text-muted">{{ $oTask->progress }}%</small>
                            </td>
                            <td><small class="text-muted">{{ $oTask->start_date?->format('d/m/Y') ?? '—' }}</small></td>
                            <td>
                                <small class="{{ $oTask->end_date?->isPast() && $oTask->progress < 100 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ $oTask->end_date?->format('d/m/Y') ?? '—' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ __('task_status.' . ($oTask->status ?? 'pending')) }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('tasks.show', $oTask) }}" class="btn btn-outline-secondary" title="{{ __('Voir') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $oTask)
                                        <a href="{{ route('tasks.edit', $oTask) }}" class="btn btn-outline-primary" title="{{ __('Modifier') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $oTask)
                                        <button type="button" class="btn btn-outline-danger" title="{{ __('Supprimer') }}"
                                                data-bs-toggle="modal" data-bs-target="#delTask{{ $oTask->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @can('delete', $oTask)
                        <div class="modal fade" id="delTask{{ $oTask->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title text-danger">{{ __('Supprimer ?') }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body small">{{ $oTask->title }}</div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                        <form method="POST" action="{{ route('tasks.destroy', $oTask) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                                {{ __('Aucune tâche trouvée') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($oTasks->hasPages())
        <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                {{ __('Page') }} {{ $oTasks->currentPage() }} / {{ $oTasks->lastPage() }}
            </span>
            {{ $oTasks->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
