@extends('layouts.app')

@section('title', __('Saisie de temps'))
@section('page-title', __('Saisie de temps'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('Saisie de temps') }}</li>
@endsection

@section('content')
<div class="row g-3">
    {{-- Formulaire de saisie rapide --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3 border-start border-primary border-3">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>{{ __('Saisie rapide') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('time-entries.store') }}" id="timeEntryForm">
                    @csrf

                    {{-- Tâche --}}
                    <div class="mb-3">
                        <label for="task_id" class="form-label fw-medium">
                            {{ __('Tâche') }} <span class="text-danger">*</span>
                        </label>
                        <select id="task_id" name="task_id"
                                class="form-select @error('task_id') is-invalid @enderror" required>
                            <option value="">{{ __('Sélectionner une tâche…') }}</option>
                            @if(!empty($aMyTasks) && $aMyTasks->isNotEmpty())
                                <optgroup label="{{ __('Mes tâches planifiées') }}">
                                    @foreach($aMyTasks->where('start_date', '<=', now())->where('end_date', '>=', now()) as $oTask)
                                        <option value="{{ $oTask->id }}" {{ old('task_id') == $oTask->id ? 'selected' : '' }}>
                                            {{ Str::limit($oTask->title, 45) }}
                                            @if($oTask->requestA4) ({{ $oTask->requestA4->reference }}) @endif
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="{{ __('Toutes mes tâches') }}">
                                    @foreach($aMyTasks as $oTask)
                                        <option value="{{ $oTask->id }}" {{ old('task_id') == $oTask->id ? 'selected' : '' }}>
                                            {{ Str::limit($oTask->title, 45) }}
                                            @if($oTask->requestA4) ({{ $oTask->requestA4->reference }}) @endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        @error('task_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Date --}}
                    <div class="mb-3">
                        <label for="entry_date" class="form-label fw-medium">
                            {{ __('Date') }} <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="entry_date" name="entry_date"
                               class="form-control @error('entry_date') is-invalid @enderror"
                               value="{{ old('entry_date', date('Y-m-d')) }}" required>
                        @error('entry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Heures --}}
                    <div class="mb-3">
                        <label for="hours" class="form-label fw-medium">
                            {{ __('Heures') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="hours" name="hours"
                                   class="form-control @error('hours') is-invalid @enderror"
                                   min="0.25" max="24" step="0.25"
                                   value="{{ old('hours', 1) }}" required>
                            <span class="input-group-text">h</span>
                        </div>
                        @error('hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        {{-- Raccourcis --}}
                        <div class="d-flex gap-1 mt-1">
                            @foreach([0.5, 1, 2, 4, 7, 8] as $nH)
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-hour-shortcut"
                                        style="font-size: 0.7rem; padding: 0.1rem 0.4rem;"
                                        data-hours="{{ $nH }}">{{ $nH }}h</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Commentaire --}}
                    <div class="mb-3">
                        <label for="comment" class="form-label fw-medium">{{ __('Commentaire') }}</label>
                        <textarea id="comment" name="comment" rows="3"
                                  class="form-control @error('comment') is-invalid @enderror"
                                  placeholder="{{ __('Description du travail effectué…') }}">{{ old('comment') }}</textarea>
                        @error('comment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-clock-history me-1"></i>{{ __('Enregistrer') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Résumé de la semaine --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2 text-success"></i>{{ __('Cette semaine') }}
                </h5>
            </div>
            <div class="card-body">
                @php $nWeeklyTotal = $oEntries->where('entry_date', '>=', now()->startOfWeek()->format('Y-m-d'))->sum('hours'); @endphp
                <div class="text-center mb-3">
                    <span class="display-6 fw-bold text-primary">{{ number_format($nWeeklyTotal, 1) }}</span>
                    <span class="text-muted">h</span>
                    <p class="text-muted small mb-0">{{ __('reportées cette semaine') }}</p>
                </div>
                <div class="progress" style="height: 8px;">
                    @php $nWeeklyPct = min(100, round($nWeeklyTotal / 35 * 100)); @endphp
                    <div class="progress-bar {{ $nWeeklyTotal > 35 ? 'bg-danger' : 'bg-success' }}"
                         role="progressbar" style="width: {{ $nWeeklyPct }}%"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted">0h</small>
                    <small class="text-muted">35h</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des saisies --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2 text-secondary"></i>{{ __('Mes saisies') }}
                </h5>
                <span class="badge bg-primary">
                    {{ $oEntries->total() }} {{ __('entrées') }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Date') }}</th>
                                <th>{{ __('Tâche') }}</th>
                                <th>{{ __('Fiche A4') }}</th>
                                <th class="text-end">{{ __('Heures') }}</th>
                                <th>{{ __('Commentaire') }}</th>
                                <th class="text-end pe-3">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sCurrentWeek = null; $nWeekSum = 0; @endphp
                            @forelse($oEntries as $oEntry)
                                @php
                                    $sEntryWeek = $oEntry->entry_date->format('Y-W');
                                    if ($sCurrentWeek !== null && $sCurrentWeek !== $sEntryWeek) {
                                @endphp
                                <tr class="table-light">
                                    <td colspan="3" class="ps-3 fw-bold small text-muted">
                                        {{ __('Semaine') }} {{ $oEntry->entry_date->format('W') }}
                                    </td>
                                    <td class="text-end fw-bold text-primary small">{{ number_format($nWeekSum, 1) }}h</td>
                                    <td colspan="2"></td>
                                </tr>
                                @php $nWeekSum = 0; } $sCurrentWeek = $sEntryWeek; $nWeekSum += $oEntry->hours; @endphp

                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-medium small">{{ $oEntry->entry_date->format('d/m/Y') }}</span>
                                        <small class="text-muted d-block">{{ $oEntry->entry_date->translatedFormat('l') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('tasks.show', $oEntry->task) }}" class="text-decoration-none small fw-medium">
                                            {{ Str::limit($oEntry->task->title ?? '—', 40) }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($oEntry->task?->requestA4)
                                            <a href="{{ route('requests.show', $oEntry->task->requestA4) }}" class="text-decoration-none">
                                                <code class="text-primary small">{{ $oEntry->task->requestA4->reference }}</code>
                                            </a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ number_format($oEntry->hours, 2) }}h</span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ Str::limit($oEntry->comment, 50) }}</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            @can('update', $oEntry)
                                                <a href="{{ route('time-entries.edit', $oEntry) }}"
                                                   class="btn btn-outline-primary" title="{{ __('Modifier') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $oEntry)
                                                <form method="POST" action="{{ route('time-entries.destroy', $oEntry) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger"
                                                            title="{{ __('Supprimer') }}"
                                                            onclick="return confirm('{{ __('Supprimer cette saisie ?') }}')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-clock d-block fs-2 mb-2"></i>
                                        {{ __('Aucune saisie de temps') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($oEntries->hasPages())
                <div class="card-footer bg-transparent border-0">
                    {{ $oEntries->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Raccourcis heures
    document.querySelectorAll('.btn-hour-shortcut').forEach(function (elBtn) {
        elBtn.addEventListener('click', function () {
            const elHours = document.getElementById('hours');
            if (elHours) elHours.value = this.dataset.hours;
        });
    });
})();
</script>
@endpush
