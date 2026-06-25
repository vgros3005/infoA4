@extends('layouts.app')

@section('title', __('Nouvelle équipe'))
@section('page-title', __('Nouvelle équipe'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teams.index') }}">{{ __('Équipes') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Créer') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>{{ __('Informations') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.teams.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">
                            {{ __('Nom de l\'équipe') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-medium">{{ __('Description') }}</label>
                        <textarea id="description" name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-people me-2"></i>{{ __('Membres initiaux') }}
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="addMemberRow">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter') }}
                        </button>
                    </h6>

                    <div id="membersContainer">
                        <div class="member-row row g-2 mb-2" data-index="0">
                            <div class="col-6">
                                <select name="members[0][user_id]" class="form-select form-select-sm">
                                    <option value="">{{ __('Utilisateur…') }}</option>
                                    @foreach($aUsers ?? [] as $oUser)
                                        <option value="{{ $oUser->id }}">{{ $oUser->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-5">
                                <select name="members[0][role_id]" class="form-select form-select-sm">
                                    <option value="">{{ __('Rôle…') }}</option>
                                    @foreach($aRoles ?? [] as $oRole)
                                        <option value="{{ $oRole->id }}">{{ $oRole->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-1">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-member">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-save me-1"></i>{{ __('Créer l\'équipe') }}
                        </button>
                        <a href="{{ route('admin.teams.index') }}" class="btn btn-outline-secondary">
                            {{ __('Annuler') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    let iIdx = 1;
    const elContainer = document.getElementById('membersContainer');

    document.getElementById('addMemberRow').addEventListener('click', function () {
        const elRow = document.createElement('div');
        elRow.className = 'member-row row g-2 mb-2';
        elRow.innerHTML = `
            <div class="col-6">
                <select name="members[${iIdx}][user_id]" class="form-select form-select-sm">
                    <option value="">{{ __("Utilisateur…") }}</option>
                    @foreach($aUsers ?? [] as $oUser)
                    <option value="{{ $oUser->id }}">{{ $oUser->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-5">
                <select name="members[${iIdx}][role_id]" class="form-select form-select-sm">
                    <option value="">{{ __("Rôle…") }}</option>
                    @foreach($aRoles ?? [] as $oRole)
                    <option value="{{ $oRole->id }}">{{ $oRole->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-member">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        elContainer.appendChild(elRow);
        iIdx++;
    });

    elContainer.addEventListener('click', function (oEvent) {
        const elBtn = oEvent.target.closest('.btn-remove-member');
        if (elBtn) {
            const elRow = elBtn.closest('.member-row');
            if (elContainer.querySelectorAll('.member-row').length > 1) {
                elRow.remove();
            }
        }
    });
})();
</script>
@endpush
