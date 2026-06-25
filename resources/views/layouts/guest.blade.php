<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Fiches A4') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page bg-body-secondary">
<div class="login-box">
    <div class="text-center mb-4">
        <i class="bi bi-file-earmark-text text-primary" style="font-size:3rem;"></i>
        <h1 class="h3 fw-bold mt-2">{{ config('app.name') }}</h1>
        <p class="text-muted">{{ __('Gestion & Suivi des Fiches A4') }}</p>
    </div>
    <div class="card shadow">
        <div class="card-body">
            {{ $slot }}
        </div>
    </div>
    <p class="text-center text-muted mt-3 small">&copy; {{ date('Y') }} DSI</p>
</div>
</body>
</html>
