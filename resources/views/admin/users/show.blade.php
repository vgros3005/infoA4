@extends('layouts.app')

@section('title', __('Utilisateur') . ' — ' . $oUser->full_name)
@section('page-title', $oUser->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{ __('Utilisateurs') }}</a></li>
    <li class="breadcrumb-item active">{{ $oUser->full_name }}</li>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 72px; height: 72px;">
                    <span class="fs-2 text-primary fw-bold">{{ strtoupper(substr($oUser->name, 0, 1)) }}</span>
                </div>
                <h5 class="mb-1">{{ $oUser->full_name }}</h5>
                <p class="text-muted small mb-2">{{ $oUser->email }}</p>
                @if($oUser->phone)
                    <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i>{{ $oUser->phone }}</p>
                @endif
                <span class="badge {{ $oUser->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $oUser->is_active ? __('Actif') : __('Inactif') }}
                </span>
                @if($oUser->deleted_at)
                    <span class="badge bg-danger ms-1">{{ __('Supprimé') }}</span>
                @endif
            </div>
            <div class="card-footer bg-transparent text-center py-2">
                <a href="{{ route('admin.users.edit', $oUser->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-diagram-3 me-2 text-secondary"></i>{{ __('Équipes & Rôles') }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Équipe') }}</th>
                                <th>{{ __('Rôle') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($oUser->teamUserRoles as $oTUR)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('admin.teams.edit', $oTUR->team_id) }}" class="fw-medium">
                                            {{ $oTUR->team->name ?? '—' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $oTUR->role->name ?? '—' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">
                                        {{ __('Aucune équipe assignée') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
