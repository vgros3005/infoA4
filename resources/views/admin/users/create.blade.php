@extends('layouts.app')

@section('title', __('Nouvel utilisateur'))
@section('page-title', __('Nouvel utilisateur'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{ __('Utilisateurs') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Créer') }}</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person me-2 text-primary"></i>{{ __('Informations personnelles') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label fw-medium">
                                {{ __('Prénom') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="first_name" name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name') }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label fw-medium">
                                {{ __('Nom') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="last_name" name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name') }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium">
                                {{ __('Email') }} <span class="text-danger">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-medium">
                                {{ __('Mot de passe') }} <span class="text-danger">*</span>
                            </label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   autocomplete="new-password" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-medium">
                                {{ __('Confirmation') }} <span class="text-danger">*</span>
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active"
                                       name="is_active" value="1" checked>
                                <label class="form-check-label fw-medium" for="is_active">
                                    {{ __('Compte actif') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            {{-- Équipes et rôles --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-diagram-3 me-2 text-secondary"></i>{{ __('Équipes & Rôles') }}
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addTeamRow">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter') }}
                    </button>
                </div>
                <div class="card-body">
                    <div id="teamRolesContainer">
                        <div class="team-role-row row g-2 mb-2" data-index="0">
                            <div class="col-6">
                                <select name="team_roles[0][team_id]" class="form-select form-select-sm">
                                    <option value="">{{ __('Équipe…') }}</option>
                                    @foreach($aTeams ?? [] as $oTeam)
                                        <option value="{{ $oTeam->id }}">{{ $oTeam->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-5">
                                <select name="team_roles[0][role_id]" class="form-select form-select-sm">
                                    <option value="">{{ __('Rôle…') }}</option>
                                    @foreach($aRoles ?? [] as $oRole)
                                        <option value="{{ $oRole->id }}">{{ $oRole->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-1">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-text">{{ __('Vous pouvez attribuer plusieurs équipes avec des rôles différents.') }}</div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>{{ __('Créer l\'utilisateur') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>{{ __('Annuler') }}
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    let iRowIndex = 1;

    const elContainer = document.getElementById('teamRolesContainer');
    const elAddBtn = document.getElementById('addTeamRow');

    const sTeamOptions = `@foreach($aTeams ?? [] as $oTeam)<option value="{{ $oTeam->id }}">{{ $oTeam->name }}</option>@endforeach`;
    const sRoleOptions = `@foreach($aRoles ?? [] as $oRole)<option value="{{ $oRole->id }}">{{ $oRole->name }}</option>@endforeach`;

    elAddBtn.addEventListener('click', function () {
        const elRow = document.createElement('div');
        elRow.className = 'team-role-row row g-2 mb-2';
        elRow.dataset.index = iRowIndex;
        elRow.innerHTML = `
            <div class="col-6">
                <select name="team_roles[${iRowIndex}][team_id]" class="form-select form-select-sm">
                    <option value="">{{ __("Équipe…") }}</option>
                    ${sTeamOptions}
                </select>
            </div>
            <div class="col-5">
                <select name="team_roles[${iRowIndex}][role_id]" class="form-select form-select-sm">
                    <option value="">{{ __("Rôle…") }}</option>
                    ${sRoleOptions}
                </select>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        elContainer.appendChild(elRow);
        iRowIndex++;
    });

    elContainer.addEventListener('click', function (oEvent) {
        const elBtn = oEvent.target.closest('.btn-remove-row');
        if (elBtn) {
            const elRow = elBtn.closest('.team-role-row');
            if (elContainer.querySelectorAll('.team-role-row').length > 1) {
                elRow.remove();
            }
        }
    });
})();
</script>
@endpush
