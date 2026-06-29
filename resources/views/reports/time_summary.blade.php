@extends('layouts.app')

@section('title', __('Récapitulatif des temps'))
@section('page-title', __('Récapitulatif des temps'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reporting') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Récap. temps') }}</li>
@endsection

@section('content')

{{-- En-tête avec liens d'impression et accès rapide --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <span class="badge bg-secondary">
            <i class="bi bi-calendar-week me-1"></i>
            {{ __('Semaine du') }} {{ $dWeekStart->translatedFormat('d F') }}
            {{ __('au') }} {{ $dWeekEnd->translatedFormat('d F Y') }}
        </span>
    </div>
    <button class="btn btn-sm btn-outline-secondary d-print-none" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>{{ __('Imprimer') }}
    </button>
</div>

{{-- Navigation entre sections --}}
<ul class="nav nav-tabs mb-4 d-print-none" id="timeSummaryTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-yesterday" data-bs-toggle="tab"
                data-bs-target="#panel-yesterday" type="button" role="tab">
            <i class="bi bi-calendar-day me-1"></i>{{ $sYesterdayLabel }}
            <span class="badge bg-secondary ms-1">{{ number_format($nYesterdayTotal, 1) }}h</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-week" data-bs-toggle="tab"
                data-bs-target="#panel-week" type="button" role="tab">
            <i class="bi bi-calendar-week me-1"></i>{{ __('Semaine en cours') }}
            <span class="badge bg-primary ms-1">{{ number_format($nWeekTotal, 1) }}h</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-requests" data-bs-toggle="tab"
                data-bs-target="#panel-requests" type="button" role="tab">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>{{ __('Par demande') }}
            <span class="badge bg-info ms-1">{{ $aByRequest->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ====================================================
         PANNEAU 1 : HIER / VENDREDI
    ===================================================== --}}
    <div class="tab-pane fade show active" id="panel-yesterday" role="tabpanel">

        <h5 class="fw-semibold mb-3 d-none d-print-block">
            <i class="bi bi-calendar-day me-2"></i>{{ $sYesterdayLabel }}
        </h5>

        @if(empty($aYesterdayByTeam))
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                    {{ __('Aucune saisie de temps pour cette journée.') }}
                </div>
            </div>
        @else
            @foreach($aYesterdayByTeam as $sTeamName => $aTeam)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="bi bi-people-fill me-2 text-primary"></i>{{ $sTeamName }}
                        </h6>
                        <span class="badge bg-primary rounded-pill">
                            {{ number_format($aTeam['team_total'], 1) }}h
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('Collaborateur') }}</th>
                                    <th class="text-end pe-3">{{ __('Heures') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aTeam['users'] as $aUserRow)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-initials avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width:32px; height:32px; font-size:.7rem; font-weight:600; flex-shrink:0;">
                                                    {{ strtoupper(substr($aUserRow['user']?->first_name ?? '?', 0, 1) . substr($aUserRow['user']?->last_name ?? '', 0, 1)) }}
                                                </div>
                                                <span class="fw-medium small">{{ $aUserRow['user']?->full_name ?? __('Utilisateur supprimé') }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="fw-semibold">{{ number_format($aUserRow['total_hours'], 1) }}h</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="ps-3 fw-semibold text-muted small">{{ __('Total') }} {{ $sTeamName }}</td>
                                    <td class="text-end pe-3 fw-bold text-primary">{{ number_format($aTeam['team_total'], 1) }}h</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach

            {{-- Grand total --}}
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">
                        <i class="bi bi-sigma me-2"></i>{{ __('Total général') }} — {{ $sYesterdayLabel }}
                    </span>
                    <span class="fs-5 fw-bold">{{ number_format($nYesterdayTotal, 1) }}h</span>
                </div>
            </div>
        @endif
    </div>

    {{-- ====================================================
         PANNEAU 2 : SEMAINE EN COURS
    ===================================================== --}}
    <div class="tab-pane fade" id="panel-week" role="tabpanel">

        <h5 class="fw-semibold mb-3 d-none d-print-block">
            <i class="bi bi-calendar-week me-2"></i>
            {{ __('Semaine du') }} {{ $dWeekStart->translatedFormat('d F') }}
            {{ __('au') }} {{ $dWeekEnd->translatedFormat('d F Y') }}
        </h5>

        @if(empty($aWeekByTeam))
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                    {{ __('Aucune saisie de temps cette semaine.') }}
                </div>
            </div>
        @else
            @foreach($aWeekByTeam as $sTeamName => $aTeam)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="bi bi-people-fill me-2 text-primary"></i>{{ $sTeamName }}
                        </h6>
                        <span class="badge bg-primary rounded-pill">
                            {{ number_format($aTeam['team_total'], 1) }}h
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('Collaborateur') }}</th>
                                    <th class="text-end pe-3">{{ __('Heures') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aTeam['users'] as $aUserRow)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-initials bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width:32px; height:32px; font-size:.7rem; font-weight:600; flex-shrink:0;">
                                                    {{ strtoupper(substr($aUserRow['user']?->first_name ?? '?', 0, 1) . substr($aUserRow['user']?->last_name ?? '', 0, 1)) }}
                                                </div>
                                                <span class="fw-medium small">{{ $aUserRow['user']?->full_name ?? __('Utilisateur supprimé') }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="fw-semibold">{{ number_format($aUserRow['total_hours'], 1) }}h</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="ps-3 fw-semibold text-muted small">{{ __('Total') }} {{ $sTeamName }}</td>
                                    <td class="text-end pe-3 fw-bold text-primary">{{ number_format($aTeam['team_total'], 1) }}h</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach

            {{-- Grand total --}}
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">
                        <i class="bi bi-sigma me-2"></i>{{ __('Total général — semaine en cours') }}
                    </span>
                    <span class="fs-5 fw-bold">{{ number_format($nWeekTotal, 1) }}h</span>
                </div>
            </div>
        @endif
    </div>

    {{-- ====================================================
         PANNEAU 3 : PAR DEMANDE + TYPE D'ACTIVITÉ
    ===================================================== --}}
    <div class="tab-pane fade" id="panel-requests" role="tabpanel">

        <h5 class="fw-semibold mb-3 d-none d-print-block">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>{{ __('Par demande — semaine en cours') }}
        </h5>

        @if($aByRequest->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                    {{ __('Aucune saisie liée à une demande cette semaine.') }}
                </div>
            </div>
        @else
            {{-- Tableau synthèse --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-table me-2 text-secondary"></i>{{ __('Synthèse') }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Demande') }}</th>
                                <th>{{ __('Titre') }}</th>
                                @php
                                    $aAllTypes = $aByRequest->flatMap(fn($r) => $r['by_task_type'])
                                        ->sortByDesc('total_hours')
                                        ->unique(fn($t) => $t['task_type']?->id ?? 0)
                                        ->values();
                                @endphp
                                @foreach($aAllTypes as $aTypeCol)
                                    <th class="text-end small text-muted">
                                        {{ $aTypeCol['task_type']?->name ?? __('Autre') }}
                                    </th>
                                @endforeach
                                <th class="text-end pe-3 fw-bold">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aByRequest as $aRow)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('requests.show', $aRow['request']) }}" class="text-decoration-none">
                                            <code class="text-primary fw-bold small">{{ $aRow['request']->reference }}</code>
                                        </a>
                                    </td>
                                    <td class="small">{{ Str::limit($aRow['request']->title, 45) }}</td>
                                    @foreach($aAllTypes as $aTypeCol)
                                        @php
                                            $nTypeHours = collect($aRow['by_task_type'])
                                                ->firstWhere(fn($t) => ($t['task_type']?->id ?? 0) === ($aTypeCol['task_type']?->id ?? 0));
                                        @endphp
                                        <td class="text-end small">
                                            {{ $nTypeHours ? number_format($nTypeHours['total_hours'], 1) . 'h' : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end pe-3 fw-bold">{{ number_format($aRow['total_hours'], 1) }}h</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td class="ps-3" colspan="2">{{ __('Total') }}</td>
                                @foreach($aAllTypes as $aTypeCol)
                                    @php
                                        $nColTotal = $aByRequest->sum(function ($aRow) use ($aTypeCol) {
                                            $found = collect($aRow['by_task_type'])
                                                ->firstWhere(fn($t) => ($t['task_type']?->id ?? 0) === ($aTypeCol['task_type']?->id ?? 0));
                                            return $found ? $found['total_hours'] : 0;
                                        });
                                    @endphp
                                    <td class="text-end small text-primary">{{ number_format($nColTotal, 1) }}h</td>
                                @endforeach
                                <td class="text-end pe-3 text-primary">
                                    {{ number_format($aByRequest->sum('total_hours'), 1) }}h
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Détail par demande --}}
            <h6 class="fw-semibold text-muted mb-3">
                <i class="bi bi-card-list me-2"></i>{{ __('Détail par demande') }}
            </h6>

            <div class="row g-3">
                @foreach($aByRequest as $aRow)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <a href="{{ route('requests.show', $aRow['request']) }}" class="text-decoration-none">
                                            <code class="text-primary fw-bold">{{ $aRow['request']->reference }}</code>
                                        </a>
                                        <p class="mb-0 small fw-medium text-body mt-1">
                                            {{ Str::limit($aRow['request']->title, 55) }}
                                        </p>
                                    </div>
                                    <span class="badge bg-primary rounded-pill ms-2 flex-shrink-0">
                                        {{ number_format($aRow['total_hours'], 1) }}h
                                    </span>
                                </div>
                            </div>
                            <div class="card-body pt-2 pb-1">
                                @foreach($aRow['by_task_type'] as $aTypeRow)
                                    @php
                                        $nPct = $aRow['total_hours'] > 0
                                            ? round($aTypeRow['total_hours'] / $aRow['total_hours'] * 100)
                                            : 0;
                                        $sColor = $aTypeRow['task_type']?->color ?? 'secondary';
                                    @endphp
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted">
                                                @if($aTypeRow['task_type']?->icon)
                                                    <i class="bi bi-{{ $aTypeRow['task_type']->icon }} me-1"></i>
                                                @endif
                                                {{ $aTypeRow['task_type']?->name ?? __('Autre') }}
                                            </span>
                                            <span class="small fw-semibold">{{ number_format($aTypeRow['total_hours'], 1) }}h</span>
                                        </div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar"
                                                 style="width: {{ $nPct }}%; background-color: var(--bs-{{ $sColor }});">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center py-1">
                                @if($aRow['request']->status)
                                    <span class="badge rounded-pill"
                                          style="background-color: var(--bs-{{ $aRow['request']->status->color ?? 'secondary' }});">
                                        {{ $aRow['request']->status->translated_label }}
                                    </span>
                                @endif
                                <a href="{{ route('requests.show', $aRow['request']) }}"
                                   class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">
                                    <i class="bi bi-eye me-1"></i>{{ __('Voir') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($nWeekNoRequestHours > 0)
                <div class="alert alert-light border mt-3 d-flex align-items-center gap-2 small">
                    <i class="bi bi-info-circle text-muted"></i>
                    {{ __(':hours heures saisies cette semaine ne sont pas liées à une demande (tâches indépendantes).',
                        ['hours' => number_format($nWeekNoRequestHours, 1)]) }}
                </div>
            @endif
        @endif
    </div>

</div>{{-- .tab-content --}}
@endsection

@push('styles')
<style>
@media print {
    .tab-pane { display: block !important; opacity: 1 !important; }
    .nav-tabs  { display: none !important; }
    .card { break-inside: avoid; }
    .btn  { display: none !important; }
}
</style>
@endpush
