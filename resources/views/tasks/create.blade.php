@extends('layouts.app')

@section('title', __('Nouvelle tâche'))
@section('page-title', __('Nouvelle tâche'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">{{ __('Tâches') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Créer') }}</li>
@endsection

@section('content')
<form method="POST" action="{{ route('tasks.store') }}" id="taskForm">
    @csrf

    <div class="row g-3">
        {{-- Colonne principale --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>{{ __('Informations') }}
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Titre --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-medium">
                            {{ __('Titre') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type et assigné --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="task_type_id" class="form-label fw-medium">
                                {{ __('Type de tâche') }} <span class="text-danger">*</span>
                            </label>
                            <select id="task_type_id" name="task_type_id"
                                    class="form-select @error('task_type_id') is-invalid @enderror" required>
                                <option value="">{{ __('Sélectionner…') }}</option>
                                @foreach($aTaskTypes ?? [] as $oType)
                                    <option value="{{ $oType->id }}" {{ old('task_type_id') == $oType->id ? 'selected' : '' }}>
                                        {{ $oType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('task_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="assigned_to" class="form-label fw-medium">{{ __('Assigné à') }}</label>
                            <select id="assigned_to" name="assigned_to"
                                    class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">{{ __('Non assigné') }}</option>
                                @foreach($aUsers ?? [] as $oUser)
                                    <option value="{{ $oUser->id }}" {{ old('assigned_to') == $oUser->id ? 'selected' : '' }}>
                                        {{ $oUser->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Planification --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-range me-2 text-secondary"></i>{{ __('Planification') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label fw-medium">{{ __('Date de début') }}</label>
                            <input type="date" id="start_date" name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label fw-medium">{{ __('Date de fin') }}</label>
                            <input type="date" id="end_date" name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="estimated_hours" class="form-label fw-medium">
                                {{ __('Heures estimées') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" id="estimated_hours" name="estimated_hours"
                                       class="form-control @error('estimated_hours') is-invalid @enderror"
                                       min="0" step="0.5" value="{{ old('estimated_hours', 1) }}" required>
                                <span class="input-group-text">h</span>
                            </div>
                            @error('estimated_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label fw-medium">{{ __('Priorité') }}</label>
                            <select id="priority" name="priority"
                                    class="form-select @error('priority') is-invalid @enderror">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('Basse') }}</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>{{ __('Normale') }}</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('Haute') }}</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>{{ __('Urgente') }}</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Avancement --}}
                    <div class="mt-3">
                        <label for="progress" class="form-label fw-medium">
                            {{ __('Avancement') }} : <span id="progressValue">{{ old('progress', 0) }}%</span>
                        </label>
                        <input type="range" id="progress" name="progress"
                               class="form-range @error('progress') is-invalid @enderror"
                               min="0" max="100" step="5"
                               value="{{ old('progress', 0) }}">
                        @error('progress')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonne latérale --}}
        <div class="col-12 col-lg-4">
            {{-- Fiche A4 liée --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-earmark-text me-2 text-secondary"></i>{{ __('Fiche A4 liée') }}
                    </h5>
                </div>
                <div class="card-body">
                    <label for="request_a4_id" class="form-label fw-medium">{{ __('Demande associée') }}</label>
                    <select id="request_a4_id" name="request_a4_id"
                            class="form-select @error('request_a4_id') is-invalid @enderror">
                        <option value="">{{ __('Tâche indépendante') }}</option>
                        @foreach($aRequests ?? [] as $oReq)
                            <option value="{{ $oReq->id }}"
                                    {{ old('request_a4_id', request('request_a4_id')) == $oReq->id ? 'selected' : '' }}>
                                {{ $oReq->reference }} — {{ Str::limit($oReq->title, 35) }}
                            </option>
                        @endforeach
                    </select>
                    @error('request_a4_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('Laissez vide pour une tâche indépendante.') }}</div>
                </div>
            </div>

            {{-- Récurrence --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-arrow-repeat me-2 text-secondary"></i>{{ __('Récurrence') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_recurring"
                               name="is_recurring" value="1"
                               {{ old('is_recurring') ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="is_recurring">
                            {{ __('Tâche récurrente') }}
                        </label>
                    </div>
                    <div id="recurringBlock" style="display: none;">
                        <label for="weekly_hours" class="form-label fw-medium">
                            {{ __('Heures prévues / semaine') }}
                        </label>
                        <div class="input-group">
                            <input type="number" id="weekly_hours" name="weekly_hours"
                                   class="form-control @error('weekly_hours') is-invalid @enderror"
                                   min="0" step="0.5" value="{{ old('weekly_hours') }}">
                            <span class="input-group-text">h/sem</span>
                        </div>
                        @error('weekly_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>{{ __('Créer la tâche') }}
                </button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
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
    // Slider avancement
    const elProgress = document.getElementById('progress');
    const elProgressValue = document.getElementById('progressValue');
    if (elProgress && elProgressValue) {
        elProgress.addEventListener('input', () => {
            elProgressValue.textContent = elProgress.value + '%';
        });
    }

    // Toggle récurrence
    const elRecurring = document.getElementById('is_recurring');
    const elRecurringBlock = document.getElementById('recurringBlock');
    if (elRecurring && elRecurringBlock) {
        const toggle = () => {
            elRecurringBlock.style.display = elRecurring.checked ? 'block' : 'none';
        };
        elRecurring.addEventListener('change', toggle);
        toggle();
    }
})();
</script>
@endpush
