@extends('layouts.app')

@section('title', __('Journal d\'activité'))
@section('page-title', __('Journal d\'activité'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Journal') }}</li>
@endsection

@section('content')
{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.logs.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">{{ __('Utilisateur') }}</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach($aUsers ?? [] as $oUser)
                            <option value="{{ $oUser->id }}" {{ request('user_id') == $oUser->id ? 'selected' : '' }}>
                                {{ $oUser->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Action') }}</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">{{ __('Toutes') }}</option>
                        @foreach($aActions ?? [] as $sAction)
                            <option value="{{ $sAction }}" {{ request('action') === $sAction ? 'selected' : '' }}>
                                {{ $sAction }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Modèle') }}</label>
                    <select name="model" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach($aModels ?? [] as $sModel)
                            <option value="{{ $sModel }}" {{ request('model') === $sModel ? 'selected' : '' }}>
                                {{ class_basename($sModel) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Depuis') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Jusqu\'au') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-funnel"></i></button>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tableau des logs --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-journal-text me-2 text-secondary"></i>{{ __('Entrées du journal') }}
        </h5>
        <span class="badge bg-secondary">{{ $oLogs->total() }} {{ __('entrées') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 150px;">{{ __('Date / Heure') }}</th>
                        <th style="width: 160px;">{{ __('Utilisateur') }}</th>
                        <th style="width: 100px;">{{ __('Action') }}</th>
                        <th style="width: 140px;">{{ __('Modèle') }}</th>
                        <th style="width: 100px;">{{ __('ID') }}</th>
                        <th>{{ __('Détails') }}</th>
                        <th style="width: 120px;">{{ __('IP') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oLogs as $oLog)
                        <tr>
                            <td class="ps-3">
                                <span class="small fw-medium">{{ $oLog->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $oLog->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <span class="small fw-medium">{{ $oLog->user->full_name ?? __('Système') }}</span>
                            </td>
                            <td>
                                @php
                                    $aActionColors = [
                                        'created' => 'success',
                                        'updated' => 'primary',
                                        'deleted' => 'danger',
                                        'login' => 'info',
                                        'logout' => 'secondary',
                                        'viewed' => 'light',
                                    ];
                                    $sColor = $aActionColors[$oLog->action ?? ''] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $sColor }} {{ $sColor === 'light' ? 'text-dark border' : '' }}">
                                    {{ $oLog->action ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <code class="small text-muted">{{ class_basename($oLog->model_type ?? '—') }}</code>
                            </td>
                            <td>
                                @if($oLog->model_id)
                                    <span class="badge bg-light text-dark border small">{{ $oLog->model_id }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($oLog->properties)
                                    <button type="button" class="btn btn-xs btn-outline-secondary"
                                            style="font-size: 0.7rem; padding: 0.1rem 0.5rem;"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#logDetails{{ $oLog->id }}">
                                        <i class="bi bi-chevron-down"></i> {{ __('Voir') }}
                                    </button>
                                    <div class="collapse mt-1" id="logDetails{{ $oLog->id }}">
                                        <pre class="bg-light rounded p-2 mb-0" style="font-size: 0.7rem; max-height: 150px; overflow-y: auto;">{{ json_encode($oLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @else
                                    <span class="text-muted small">{{ $oLog->description ?? '—' }}</span>
                                @endif
                            </td>
                            <td>
                                <code class="small text-muted">{{ $oLog->ip_address ?? '—' }}</code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-journal-x d-block fs-2 mb-2"></i>
                                {{ __('Aucune entrée dans le journal') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($oLogs->hasPages())
        <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                {{ __('Page') }} {{ $oLogs->currentPage() }} / {{ $oLogs->lastPage() }}
                — {{ $oLogs->total() }} {{ __('entrées au total') }}
            </span>
            {{ $oLogs->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
