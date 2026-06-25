@extends('layouts.app')

@section('title', __('Utilisateurs'))
@section('page-title', __('Gestion des utilisateurs'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Utilisateurs') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $oUsers->total() }} {{ __('utilisateur(s)') }}</span>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i>{{ __('Nouvel utilisateur') }}
    </a>
</div>

{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="{{ __('Nom, email…') }}" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="team_id" class="form-select form-select-sm">
                        <option value="">{{ __('Toutes les équipes') }}</option>
                        @foreach($aTeams ?? [] as $oTeam)
                            <option value="{{ $oTeam->id }}" {{ request('team_id') == $oTeam->id ? 'selected' : '' }}>
                                {{ $oTeam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="active" class="form-select form-select-sm">
                        <option value="">{{ __('Tous') }}</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>{{ __('Actifs') }}</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>{{ __('Inactifs') }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-funnel"></i></button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Nom') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Équipes / Rôles') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Créé le') }}</th>
                        <th class="text-end pe-3">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oUsers as $oUser)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium">{{ $oUser->full_name }}</div>
                            </td>
                            <td><small class="text-muted">{{ $oUser->email }}</small></td>
                            <td>
                                @foreach($oUser->teamUserRoles as $oTUR)
                                    <span class="badge bg-light text-dark border me-1" style="font-size: 0.7rem;">
                                        {{ $oTUR->team->name ?? '?' }}
                                        <span class="text-muted">({{ $oTUR->role->name ?? '?' }})</span>
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                @if($oUser->is_active ?? true)
                                    <span class="badge bg-success">{{ __('Actif') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactif') }}</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $oUser->created_at->format('d/m/Y') }}</small></td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $oUser) }}" class="btn btn-outline-secondary" title="{{ __('Voir') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $oUser) }}" class="btn btn-outline-primary" title="{{ __('Modifier') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($oUser->id !== auth()->id())
                                        <button type="button" class="btn btn-outline-danger"
                                                title="{{ __('Supprimer') }}"
                                                data-bs-toggle="modal" data-bs-target="#delUser{{ $oUser->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($oUser->id !== auth()->id())
                        <div class="modal fade" id="delUser{{ $oUser->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title text-danger">{{ __('Supprimer ?') }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body small">
                                        <strong>{{ $oUser->full_name }}</strong><br>
                                        {{ __('Cette action est irréversible.') }}
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $oUser) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-people d-block fs-2 mb-2"></i>
                                {{ __('Aucun utilisateur trouvé') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($oUsers->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $oUsers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
