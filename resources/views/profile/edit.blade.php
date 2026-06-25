@extends('layouts.app')

@section('title', __('ui.profile'))
@section('page-title', __('ui.profile'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('ui.profile') }}</li>
@endsection

@section('content')
<div class="row g-3">

    {{-- Informations générales + langue --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-circle me-2 text-primary"></i>{{ __('ui.profile_info') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="first_name" class="form-label fw-medium">{{ __('Prénom') }}</label>
                            <input type="text" id="first_name" name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $user->first_name) }}">
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label for="last_name" class="form-label fw-medium">{{ __('Nom') }}</label>
                            <input type="text" id="last_name" name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $user->last_name) }}">
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="name" class="form-label fw-medium">{{ __('Identifiant / Login') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-medium">{{ __('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="phone" class="form-label fw-medium">{{ __('Téléphone') }}</label>
                            <input type="text" id="phone" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Sélecteur de langue --}}
                        <div class="col-12">
                            <label class="form-label fw-medium">
                                <i class="bi bi-translate me-1"></i>{{ __('ui.language') }}
                            </label>
                            <p class="text-muted small mb-2">{{ __('ui.language_hint') }}</p>
                            <div class="row g-2">
                                @foreach(config('app.supported_locales') as $sCode => $aLocale)
                                    <div class="col-auto">
                                        <input type="radio" class="btn-check" name="locale"
                                               id="locale_{{ $sCode }}" value="{{ $sCode }}"
                                               {{ old('locale', $user->locale ?? 'fr') === $sCode ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary d-flex align-items-center gap-2"
                                               for="locale_{{ $sCode }}">
                                            <span style="font-size: 1.4rem; line-height: 1;">{{ $aLocale['flag'] }}</span>
                                            <span class="fw-medium">{{ $aLocale['native'] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('locale')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ __('ui.save') }}
                            </button>
                            @if(session('status') === 'profile-updated')
                                <span class="ms-3 text-success small">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('Profil mis à jour') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Changement de mot de passe --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-key me-2 text-warning"></i>{{ __('ui.password') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-medium">{{ __('ui.current_password') }}</label>
                        <input type="password" id="current_password" name="current_password"
                               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                               autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">{{ __('ui.new_password') }}</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                               autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-medium">{{ __('ui.confirm_password') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key me-1"></i>{{ __('Changer le mot de passe') }}
                    </button>
                    @if(session('status') === 'password-updated')
                        <span class="ms-2 text-success small">
                            <i class="bi bi-check-circle me-1"></i>{{ __('Mot de passe mis à jour') }}
                        </span>
                    @endif
                </form>
            </div>
        </div>

        {{-- Aperçu du compte --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:64px;height:64px;">
                    <span class="fs-3 fw-bold text-primary">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <h6 class="mb-1">{{ $user->full_name }}</h6>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                @php $aCurrentLocale = config('app.supported_locales')[$user->locale ?? 'fr'] ?? config('app.supported_locales')['fr']; @endphp
                <span class="badge bg-light text-dark border" style="font-size:.9rem;">
                    {{ $aCurrentLocale['flag'] }} {{ $aCurrentLocale['native'] }}
                </span>
            </div>
        </div>
    </div>

</div>
@endsection
