@extends('layouts.app')

@section('title', __('Équipe') . ' — ' . $oTeam->name)
@section('page-title', $oTeam->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teams.index') }}">{{ __('Équipes') }}</a></li>
    <li class="breadcrumb-item active">{{ $oTeam->name }}</li>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-1">{{ $oTeam->name }}</h5>
                @if($oTeam->description)
                    <p class="text-muted small mb-3">{{ $oTeam->description }}</p>
                @endif
                <dl class="row small mb-0">
                    <dt class="col-5">{{ __('Membres') }}</dt>
                    <dd class="col-7">{{ $oTeam->teamUserRoles->count() }}</dd>
                    <dt class="col-5">{{ __('Statut') }}</dt>
                    <dd class="col-7">
                        <span class="badge {{ $oTeam->is_active ?? true ? 'bg-success' : 'bg-secondary' }}">
                            {{ ($oTeam->is_active ?? true) ? __('Active') : __('Inactive') }}
                        </span>
                    </dd>
                </dl>
            </div>
            <div class="card-footer bg-transparent d-flex gap-2">
                <a href="{{ route('admin.teams.edit', $oTeam->id) }}" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}
                </a>
                <a href="{{ route('admin.teams.index') }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('Retour') }}
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people me-2 text-secondary"></i>{{ __('Membres') }}
                    <span class="badge bg-primary ms-1">{{ $oTeam->teamUserRoles->count() }}</span>
                </h5>
                <a href="{{ route('admin.teams.edit', $oTeam->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>{{ __('Gérer') }}
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Nom') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Rôle') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($oTeam->teamUserRoles as $oTUR)
                                <tr>
                                    <td class="ps-3 fw-medium">
                                        <a href="{{ route('admin.users.show', $oTUR->user_id) }}" class="text-decoration-none">
                                            {{ $oTUR->user->full_name ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="text-muted small">{{ $oTUR->user->email ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $oTUR->role->name ?? '—' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        {{ __('Aucun membre dans cette équipe') }}
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
