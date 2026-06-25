@extends('layouts.app')

@section('title', __('Fiches A4'))
@section('page-title', __('Liste des demandes'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('Fiches A4') }}</li>
@endsection

@section('content')
{{-- Barre d'outils --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">
        {{ $oRequests->total() }} {{ __('demande(s) trouvée(s)') }}
    </span>
    @can('create', \App\Models\RequestA4::class)
        <a href="{{ route('requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle demande') }}
        </a>
    @endcan
</div>

{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('requests.index') }}" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="{{ __('Référence, titre…') }}"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Statut') }}</label>
                    <select name="status_id" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach($aStatuses as $oStatus)
                            <option value="{{ $oStatus->id }}" {{ request('status_id') == $oStatus->id ? 'selected' : '' }}>
                                {{ $oStatus->translated_label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Type') }}</label>
                    <select name="type_id" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach($aTypes as $oType)
                            <option value="{{ $oType->id }}" {{ request('type_id') == $oType->id ? 'selected' : '' }}>
                                {{ $oType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Priorité') }}</label>
                    <select name="priority_id" class="form-select form-select-sm">
                        <option value="">{{ __('Toutes') }}</option>
                        @foreach($aPriorities as $oPriority)
                            <option value="{{ $oPriority->id }}" {{ request('priority_id') == $oPriority->id ? 'selected' : '' }}>
                                {{ $oPriority->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Période') }}</label>
                    <input type="month" name="period" class="form-control form-control-sm"
                           value="{{ request('period') }}">
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-secondary flex-fill">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Réinitialiser') }}">
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
                        <th class="ps-3">{{ __('Référence') }}</th>
                        <th>{{ __('Titre') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Priorité') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Demandeur') }}</th>
                        <th>{{ __('Date souhaitée') }}</th>
                        <th class="text-end pe-3">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oRequests as $oRequest)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('requests.show', $oRequest) }}" class="text-decoration-none">
                                    <code class="text-primary fw-bold">{{ $oRequest->reference }}</code>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('requests.show', $oRequest) }}" class="text-decoration-none text-dark fw-medium">
                                    {{ Str::limit($oRequest->title, 50) }}
                                </a>
                            </td>
                            <td><span class="text-muted small">{{ $oRequest->requestType->name ?? '—' }}</span></td>
                            <td>
                                <span class="badge rounded-pill" style="background-color: {{ $oRequest->priority->color ?? '#6c757d' }}">
                                    {{ $oRequest->priority->name ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill" style="background-color: {{ $oRequest->status->color ?? '#6c757d' }}">
                                    {{ $oRequest->status->translated_label ?? '—' }}
                                </span>
                            </td>
                            <td><span class="small">{{ $oRequest->requester->full_name ?? '—' }}</span></td>
                            <td>
                                @if($oRequest->desired_date)
                                    <span class="{{ $oRequest->desired_date->isPast() ? 'text-danger fw-bold' : 'text-muted' }} small">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $oRequest->desired_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('requests.show', $oRequest) }}"
                                       class="btn btn-outline-secondary" title="{{ __('Voir') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $oRequest)
                                        <a href="{{ route('requests.edit', $oRequest) }}"
                                           class="btn btn-outline-primary" title="{{ __('Modifier') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $oRequest)
                                        <button type="button" class="btn btn-outline-danger"
                                                title="{{ __('Supprimer') }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal{{ $oRequest->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        {{-- Modal suppression --}}
                        @can('delete', $oRequest)
                        <div class="modal fade" id="deleteModal{{ $oRequest->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-danger">
                                            <i class="bi bi-exclamation-triangle me-2"></i>{{ __('Confirmer la suppression') }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{ __('Supprimer la demande') }} <strong>{{ $oRequest->reference }}</strong> ?
                                        {{ __('Cette action est irréversible.') }}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            {{ __('Annuler') }}
                                        </button>
                                        <form method="POST" action="{{ route('requests.destroy', $oRequest) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-trash me-1"></i>{{ __('Supprimer') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                                {{ __('Aucune demande trouvée') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($oRequests->hasPages())
        <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                {{ __('Page') }} {{ $oRequests->currentPage() }} / {{ $oRequests->lastPage() }}
            </span>
            {{ $oRequests->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
