<x-guest-layout>
    @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <h4 class="mb-4 text-center">{{ __('Connexion') }}</h4>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Adresse e-mail') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Mot de passe') }}</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
            <label class="form-check-label" for="remember_me">{{ __('Se souvenir de moi') }}</label>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('Se connecter') }}
            </button>
        </div>

        @if(Route::has('password.request'))
            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-muted small">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            </div>
        @endif
    </form>

    <hr>
    <p class="text-center text-muted small mb-0">
        {{ __('Compte démo :') }} <code>admin@infoa4.local</code> / <code>password</code>
    </p>
</x-guest-layout>
