@extends('layouts.app')

@section('title', __('Modifier') . ' — ' . $oTeam->name)
@section('page-title', __('Modifier l\'équipe') . ' : ' . $oTeam->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teams.index') }}">{{ __('Équipes') }}</a></li>
    <li class="breadcrumb-item active">{{ $oTeam->name }}</li>
@endsection

@section('content')
<div class="row g-3">
    {{-- Infos équipe --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>{{ __('Informations') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.teams.update', $oTeam) }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">
                            {{ __('Nom') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $oTeam->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-medium">{{ __('Description') }}</label>
                        <textarea id="description" name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $oTeam->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-save me-1"></i>{{ __('Enregistrer') }}
                        </button>
                        <a href="{{ route('admin.teams.index') }}" class="btn btn-outline-secondary">
                            {{ __('Annuler') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Gestion membres --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people me-2 text-secondary"></i>
                    {{ __('Membres') }}
                    <span class="badge bg-primary ms-1">{{ $oTeam->teamUserRoles->count() }}</span>
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="bi bi-person-plus me-1"></i>{{ __('Ajouter') }}
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Membre') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Rôle') }}</th>
                                <th class="text-end pe-3">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($oTeam->teamUserRoles as $oTUR)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-medium small">{{ $oTUR->user->full_name ?? '—' }}</div>
                                    </td>
                                    <td><small class="text-muted">{{ $oTUR->user->email ?? '—' }}</small></td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.teams.members.update', [$oTeam, $oTUR]) }}"
                                              class="d-flex gap-1 align-items-center">
                                            @csrf @method('PATCH')
                                            <select name="role_id" class="form-select form-select-sm" style="min-width: 130px;">
                                                @foreach($aRoles ?? [] as $oRole)
                                                    <option value="{{ $oRole->id }}" {{ $oTUR->role_id == $oRole->id ? 'selected' : '' }}>
                                                        {{ $oRole->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="{{ __('Mettre à jour') }}">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form method="POST" action="{{ route('admin.teams.members.destroy', [$oTeam, $oTUR]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="{{ __('Retirer') }}"
                                                    onclick="return confirm('{{ __('Retirer ce membre de l\'équipe ?') }}')">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
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

{{-- Modal ajout membre --}}
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>{{ __('Ajouter un membre') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.teams.members.store', $oTeam) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-medium">
                            {{ __('Utilisateur') }} <span class="text-danger">*</span>
                        </label>
                        <select id="user_id" name="user_id" class="form-select" required>
                            <option value="">{{ __('Sélectionner un utilisateur…') }}</option>
                            @foreach($aAvailableUsers ?? [] as $oUser)
                                <option value="{{ $oUser->id }}">{{ $oUser->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="role_id" class="form-label fw-medium">
                            {{ __('Rôle') }} <span class="text-danger">*</span>
                        </label>
                        <select id="role_id" name="role_id" class="form-select" required>
                            <option value="">{{ __('Sélectionner un rôle…') }}</option>
                            @foreach($aRoles ?? [] as $oRole)
                                <option value="{{ $oRole->id }}">{{ $oRole->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>{{ __('Ajouter') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
