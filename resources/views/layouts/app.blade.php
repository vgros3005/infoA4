<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    {{-- Navbar top --}}
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="bi bi-house-door me-1"></i>{{ __('ui.home') }}
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('requests.create') }}" title="{{ __('ui.new_request') }}">
                        <i class="bi bi-plus-circle text-primary fs-5"></i>
                    </a>
                </li>

                {{-- Language switcher --}}
                @php $aSupportedLocales = config('app.supported_locales'); $sCurrentLocale = app()->getLocale(); @endphp
                <li class="nav-item dropdown me-1">
                    <a href="#" class="nav-link dropdown-toggle px-2" data-bs-toggle="dropdown" title="{{ __('ui.language') }}">
                        <span style="font-size:1.2rem;">{{ $aSupportedLocales[$sCurrentLocale]['flag'] ?? '🌐' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:140px;">
                        @foreach($aSupportedLocales as $sCode => $aLocale)
                            <li>
                                <form method="POST" action="{{ route('profile.update') }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="name"   value="{{ Auth::user()->name }}">
                                    <input type="hidden" name="email"  value="{{ Auth::user()->email }}">
                                    <input type="hidden" name="locale" value="{{ $sCode }}">
                                    <button type="submit"
                                            class="dropdown-item d-flex align-items-center gap-2 {{ $sCurrentLocale === $sCode ? 'fw-bold' : '' }}">
                                        <span style="font-size:1.1rem;">{{ $aLocale['flag'] }}</span>
                                        {{ $aLocale['native'] }}
                                        @if($sCurrentLocale === $sCode)
                                            <i class="bi bi-check2 ms-auto text-primary"></i>
                                        @endif
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </li>

                {{-- User menu --}}
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="d-none d-md-inline">{{ Auth::user()->full_name }}</span>
                        <i class="bi bi-person-circle ms-1 fs-5"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2 bg-primary text-white">
                            <p class="mb-0 fw-bold">{{ Auth::user()->full_name }}</p>
                            <small class="text-white-50">{{ Auth::user()->email }}</small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i>{{ __('ui.profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('ui.logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Sidebar --}}
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark" id="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <i class="bi bi-file-earmark-text brand-image text-primary fs-3 ms-3"></i>
                <span class="brand-text fw-bold ms-2">Fiches A4</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>{{ __('ui.dashboard') }}</p>
                        </a>
                    </li>

                    <li class="nav-header">{{ strtoupper(__('ui.nav_requests')) }}</li>

                    <li class="nav-item {{ request()->routeIs('requests.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-text"></i>
                            <p>{{ __('ui.requests') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.index') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-list-ul"></i>
                                    <p>{{ __('ui.all_requests') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('requests.create') }}" class="nav-link {{ request()->routeIs('requests.create') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-plus-circle"></i>
                                    <p>{{ __('ui.new_request') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('requests.index', ['filter' => 'mine']) }}" class="nav-link">
                                    <i class="nav-icon bi bi-person-check"></i>
                                    <p>{{ __('ui.my_requests') }}</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-header">{{ strtoupper(__('ui.nav_tasks')) }}</li>

                    <li class="nav-item {{ request()->routeIs('tasks.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-check2-square"></i>
                            <p>{{ __('ui.tasks') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-list-check"></i>
                                    <p>{{ __('ui.all_tasks') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('tasks.gantt') }}" class="nav-link {{ request()->routeIs('tasks.gantt') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-bar-chart-gantt"></i>
                                    <p>{{ __('ui.gantt_chart') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('time-entries.index') }}" class="nav-link {{ request()->routeIs('time-entries.*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-clock"></i>
                                    <p>{{ __('ui.time_entries') }}</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-header">{{ strtoupper(__('ui.nav_reports')) }}</li>

                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-graph-up"></i>
                            <p>{{ __('ui.reporting') }}</p>
                        </a>
                    </li>

                    @if(Auth::user()->isAdmin())
                    <li class="nav-header">{{ strtoupper(__('ui.nav_admin')) }}</li>

                    <li class="nav-item {{ request()->routeIs('admin.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-gear"></i>
                            <p>{{ __('ui.administration') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-people"></i><p>{{ __('ui.users') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.teams.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-diagram-3"></i><p>{{ __('ui.teams') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.statuses.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-arrow-repeat"></i><p>{{ __('ui.statuses_workflow') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.request-types.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-tags"></i><p>{{ __('ui.request_types') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.priorities.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-exclamation-circle"></i><p>{{ __('ui.priorities') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.companies.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-building"></i><p>{{ __('ui.companies') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.softwares.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-cpu"></i><p>{{ __('ui.softwares') }}</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.logs.index') }}" class="nav-link">
                                    <i class="nav-icon bi bi-journal-text"></i><p>{{ __('ui.activity_log') }}</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Contenu --}}
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">@yield('page-title', '')</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">{{ __('ui.home') }}</a>
                            </li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible auto-hide">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible auto-hide">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible auto-hide">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <strong>{{ config('app.name') }}</strong> &copy; {{ date('Y') }} DSI — v1.0.0
    </footer>
</div>
@stack('scripts')
</body>
</html>
