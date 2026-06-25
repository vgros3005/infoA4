@extends('layouts.app')

@section('title', __('Diagramme Gantt'))
@section('page-title', __('Diagramme Gantt'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">{{ __('Tâches') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Gantt') }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="/css/frappe-gantt.css">
<style>
#gantt-wrapper {
    overflow-x: auto;
    background: #fff;
    border-radius: 0.375rem;
}
#gantt-container svg {
    font-family: system-ui, -apple-system, sans-serif;
}
.gantt-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
}
.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    flex-shrink: 0;
}
.gantt .bar-progress { fill: #0d6efd; }
.gantt .bar { fill: #e9ecef; }
.gantt .bar-label { fill: #212529; font-size: 11px; }
</style>
@endpush

@section('content')
{{-- Contrôles --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('tasks.gantt') }}" id="ganttFilterForm">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">{{ __('Développeur') }}</label>
                    <select name="user_id" class="form-select form-select-sm" id="filterUser">
                        <option value="">{{ __('Tous les développeurs') }}</option>
                        @foreach($aUsers ?? [] as $oUser)
                            <option value="{{ $oUser->id }}" {{ request('user_id') == $oUser->id ? 'selected' : '' }}>
                                {{ $oUser->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">{{ __('Vue') }}</label>
                    <select name="view_mode" class="form-select form-select-sm" id="viewMode">
                        <option value="Week" {{ request('view_mode', 'Week') === 'Week' ? 'selected' : '' }}>{{ __('Semaine') }}</option>
                        <option value="Month" {{ request('view_mode') === 'Month' ? 'selected' : '' }}>{{ __('Mois') }}</option>
                        <option value="Day" {{ request('view_mode') === 'Day' ? 'selected' : '' }}>{{ __('Jour') }}</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="bi bi-funnel me-1"></i>{{ __('Filtrer') }}
                    </button>
                </div>
                <div class="col-12 col-md-5 d-flex justify-content-end align-items-end gap-2">
                    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-list-check me-1"></i>{{ __('Vue liste') }}
                    </a>
                    @can('create', \App\Models\Task::class)
                        <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle tâche') }}
                        </a>
                    @endcan
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Gantt --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-bar-chart-gantt me-2 text-primary"></i>{{ __('Planification des tâches') }}
        </h5>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnViewDay">{{ __('Jour') }}</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnViewWeek">{{ __('Semaine') }}</button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnViewMonth">{{ __('Mois') }}</button>
        </div>
    </div>
    <div class="card-body p-2">
        <div id="gantt-wrapper">
            <div id="gantt-container"></div>
        </div>
        <div id="gantt-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('Chargement…') }}</span>
            </div>
            <p class="text-muted mt-2">{{ __('Chargement du diagramme…') }}</p>
        </div>
        <div id="gantt-empty" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox d-block fs-2 text-muted mb-2"></i>
            <p class="text-muted">{{ __('Aucune tâche à afficher') }}</p>
        </div>
    </div>
</div>

{{-- Légende --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0">
        <h6 class="mb-0"><i class="bi bi-palette me-2"></i>{{ __('Légende') }}</h6>
    </div>
    <div class="card-body">
        <div class="gantt-legend" id="ganttLegend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #0d6efd;"></div>
                <span>{{ __('Développement') }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #198754;"></div>
                <span>{{ __('Tests') }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffc107;"></div>
                <span>{{ __('Analyse') }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #dc3545;"></div>
                <span>{{ __('Support') }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #6c757d;"></div>
                <span>{{ __('Autre') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="/js/frappe-gantt.js"></script>
<script>
(function () {
const sApiUrl = '/api/tasks/gantt-data';
const iUserId = {{ request('user_id', 'null') }};
const sDefaultView = '{{ request('view_mode', 'Week') }}';

let oGanttInstance = null;

const elContainer = document.getElementById('gantt-container');
const elLoading = document.getElementById('gantt-loading');
const elEmpty = document.getElementById('gantt-empty');

const aTaskColors = {
    'Développement': '#0d6efd',
    'Tests': '#198754',
    'Analyse': '#ffc107',
    'Support': '#dc3545',
};

/**
 * Charge les données Gantt depuis l'API et initialise Frappe Gantt
 * @param {string} sViewMode - Mode de vue (Day / Week / Month)
 */
async function loadGantt(sViewMode) {
    elLoading.style.display = 'block';
    elEmpty.style.display = 'none';
    elContainer.style.display = 'none';

    try {
        const sParams = new URLSearchParams();
        if (iUserId) sParams.set('user_id', iUserId);

        const oResponse = await fetch(sApiUrl + '?' + sParams.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!oResponse.ok) throw new Error('HTTP ' + oResponse.status);

        const oData = await oResponse.json();
        const aTasks = oData.data ?? oData;

        if (!aTasks.length) {
            elEmpty.style.display = 'block';
            elLoading.style.display = 'none';
            return;
        }

        // Convertir en format Frappe Gantt
        const aGanttTasks = aTasks.map(oTask => ({
            id: String(oTask.id),
            name: oTask.title,
            start: oTask.start_date,
            end: oTask.end_date,
            progress: oTask.progress ?? 0,
            dependencies: oTask.dependencies ?? '',
            custom_class: 'task-type-' + (oTask.task_type ?? 'other').toLowerCase().replace(/\s+/g, '-'),
        }));

        elContainer.style.display = 'block';
        elLoading.style.display = 'none';

        if (oGanttInstance) {
            oGanttInstance.refresh(aGanttTasks);
            oGanttInstance.change_view_mode(sViewMode);
        } else {
            oGanttInstance = new Gantt('#gantt-container', aGanttTasks, {
                view_mode: sViewMode,
                date_format: 'YYYY-MM-DD',
                language: 'fr',
                popup_trigger: 'click',
                on_click: function (oTask) {
                    const sUrl = '/tasks/' + oTask.id;
                    window.location.href = sUrl;
                },
                on_date_change: function (oTask, sStart, sEnd) {
                    fetch('/api/tasks/' + oTask.id + '/dates', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ start_date: sStart, end_date: sEnd })
                    });
                },
                on_progress_change: function (oTask, nProgress) {
                    fetch('/api/tasks/' + oTask.id + '/progress', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ progress: nProgress })
                    });
                },
            });
        }
    } catch (oError) {
        elLoading.style.display = 'none';
        elEmpty.style.display = 'block';
        console.error('Gantt load error:', oError);
    }
}

// Boutons de vue
document.getElementById('btnViewDay').addEventListener('click', () => loadGantt('Day'));
document.getElementById('btnViewWeek').addEventListener('click', () => loadGantt('Week'));
document.getElementById('btnViewMonth').addEventListener('click', () => loadGantt('Month'));

// Chargement initial
loadGantt(sDefaultView);
})();
</script>
@endpush
