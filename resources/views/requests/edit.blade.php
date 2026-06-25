@extends('layouts.app')

@section('title', __('Modifier la demande') . ' — ' . $oRequest->reference)
@section('page-title', __('Modifier') . ' : ' . $oRequest->reference)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('requests.index') }}">{{ __('Fiches A4') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requests.show', $oRequest) }}">{{ $oRequest->reference }}</a></li>
    <li class="breadcrumb-item active">{{ __('Modifier') }}</li>
@endsection

@push('styles')
<style>
.tox-tinymce { border-radius: 0.375rem !important; }
.char-counter { font-size: 0.75rem; }
.char-counter.text-danger { font-weight: bold; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('requests.update', $oRequest) }}" enctype="multipart/form-data" id="requestForm">
    @csrf
    @method('PUT')

    <div class="row g-3">
        {{-- Colonne principale --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>{{ __('Informations générales') }}
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
                               maxlength="50"
                               value="{{ old('title', $oRequest->title) }}"
                               required>
                        <div class="d-flex justify-content-between mt-1">
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div></div>
                            @enderror
                            <span class="char-counter text-muted" id="titleCounter">0 / 50</span>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-medium">
                            {{ __('Description') }} <span class="text-danger">*</span>
                        </label>
                        <textarea id="description" name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  required>{{ old('description', $oRequest->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contenu WYSIWYG --}}
                    <div class="mb-3">
                        <label for="content" class="form-label fw-medium">{{ __('Contenu détaillé') }}</label>
                        <textarea id="content" name="content"
                                  class="form-control @error('content') is-invalid @enderror">{{ old('content', $oRequest->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Notes internes --}}
            @can('seeInternalNotes', $oRequest)
            <div class="card border-0 shadow-sm mb-3 border-start border-warning border-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lock me-2 text-warning"></i>{{ __('Notes internes') }}
                        <span class="badge bg-warning text-dark ms-2 small">{{ __('Admin / CP') }}</span>
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <textarea id="internal_notes" name="internal_notes" rows="3"
                              class="form-control @error('internal_notes') is-invalid @enderror">{{ old('internal_notes', $oRequest->internal_notes) }}</textarea>
                    @error('internal_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @endcan
        </div>

        {{-- Colonne latérale --}}
        <div class="col-12 col-lg-4">
            {{-- Référence (lecture seule) --}}
            <div class="card border-0 shadow-sm mb-3 bg-light">
                <div class="card-body py-2">
                    <small class="text-muted d-block">{{ __('Référence') }}</small>
                    <strong class="text-primary fs-5">{{ $oRequest->reference }}</strong>
                    <small class="text-muted d-block mt-1">
                        {{ __('Créée le') }} {{ $oRequest->created_at->format('d/m/Y') }}
                    </small>
                </div>
            </div>

            {{-- Classification --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tags me-2 text-secondary"></i>{{ __('Classification') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="request_type_id" class="form-label fw-medium">
                            {{ __('Type') }} <span class="text-danger">*</span>
                        </label>
                        <select id="request_type_id" name="request_type_id"
                                class="form-select @error('request_type_id') is-invalid @enderror" required>
                            <option value="">{{ __('Sélectionner…') }}</option>
                            @foreach($aRequestTypes as $oType)
                                <option value="{{ $oType->id }}"
                                        {{ old('request_type_id', $oRequest->request_type_id) == $oType->id ? 'selected' : '' }}>
                                    {{ $oType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('request_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="priority_id" class="form-label fw-medium">
                            {{ __('Priorité') }} <span class="text-danger">*</span>
                        </label>
                        <select id="priority_id" name="priority_id"
                                class="form-select @error('priority_id') is-invalid @enderror" required>
                            <option value="">{{ __('Sélectionner…') }}</option>
                            @foreach($aPriorities as $oPriority)
                                <option value="{{ $oPriority->id }}"
                                        data-requires-justification="{{ $oPriority->requires_justification ? '1' : '0' }}"
                                        {{ old('priority_id', $oRequest->priority_id) == $oPriority->id ? 'selected' : '' }}>
                                    {{ $oPriority->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0" id="justificationBlock" style="display: none;">
                        <label for="priority_justification" class="form-label fw-medium">
                            {{ __('Justification') }} <span class="text-danger">*</span>
                        </label>
                        <textarea id="priority_justification" name="priority_justification" rows="3"
                                  class="form-control @error('priority_justification') is-invalid @enderror">{{ old('priority_justification', $oRequest->priority_justification) }}</textarea>
                        @error('priority_justification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Périmètre --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-building me-2 text-secondary"></i>{{ __('Périmètre') }}
                    </h5>
                </div>
                <div class="card-body">
                    @php $aSelectedCompanies = old('company_ids', $oRequest->companies->pluck('id')->toArray()); @endphp
                    <div class="mb-3">
                        <label class="form-label fw-medium">{{ __('Société(s)') }} <span class="text-danger">*</span></label>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                            @foreach($aCompanies as $oCompany)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="company_ids[]" value="{{ $oCompany->id }}"
                                           id="company_{{ $oCompany->id }}"
                                           {{ in_array($oCompany->id, $aSelectedCompanies) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="company_{{ $oCompany->id }}">
                                        {{ $oCompany->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('company_ids')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    @php $aSelectedSoftwares = old('software_ids', $oRequest->softwares->pluck('id')->toArray()); @endphp
                    <div class="mb-0">
                        <label class="form-label fw-medium">{{ __('Logiciel(s)') }}</label>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                            @foreach($aSoftwares as $oSoftware)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="software_ids[]" value="{{ $oSoftware->id }}"
                                           id="software_{{ $oSoftware->id }}"
                                           {{ in_array($oSoftware->id, $aSelectedSoftwares) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="software_{{ $oSoftware->id }}">
                                        {{ $oSoftware->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar3 me-2 text-secondary"></i>{{ __('Dates') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="requested_date" class="form-label fw-medium">
                            {{ __('Date de demande') }} <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="requested_date" name="requested_date"
                               class="form-control @error('requested_date') is-invalid @enderror"
                               value="{{ old('requested_date', $oRequest->requested_date?->format('Y-m-d')) }}" required>
                        @error('requested_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="desired_date" class="form-label fw-medium">{{ __('Date souhaitée') }}</label>
                        <input type="date" id="desired_date" name="desired_date"
                               class="form-control @error('desired_date') is-invalid @enderror"
                               value="{{ old('desired_date', $oRequest->desired_date?->format('Y-m-d')) }}">
                        @error('desired_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>{{ __('Enregistrer les modifications') }}
                </button>
                <a href="{{ route('requests.show', $oRequest) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>{{ __('Annuler') }}
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    const elTitle = document.getElementById('title');
    const elCounter = document.getElementById('titleCounter');
    if (!elTitle || !elCounter) return;

    const updateCounter = () => {
        const iLen = elTitle.value.length;
        elCounter.textContent = iLen + ' / 50';
        elCounter.classList.toggle('text-danger', iLen >= 45);
        elCounter.classList.toggle('text-muted', iLen < 45);
    };

    elTitle.addEventListener('input', updateCounter);
    updateCounter();
})();

(function () {
    const elPriority = document.getElementById('priority_id');
    const elBlock = document.getElementById('justificationBlock');
    const elJustif = document.getElementById('priority_justification');
    if (!elPriority || !elBlock) return;

    const toggleJustification = () => {
        const elSelected = elPriority.options[elPriority.selectedIndex];
        const bRequired = elSelected && elSelected.dataset.requiresJustification === '1';
        elBlock.style.display = bRequired ? 'block' : 'none';
        if (elJustif) elJustif.required = bRequired;
    };

    elPriority.addEventListener('change', toggleJustification);
    toggleJustification();
})();

tinymce.init({
    selector: '#content',
    plugins: 'lists link image table code',
    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
    base_url: '/tinymce',
    height: 400,
    menubar: false,
    branding: false,
    resize: true,
    content_style: 'body { font-family: system-ui, -apple-system, sans-serif; font-size: 14px; }',
    images_upload_handler: function (oBlobInfo, fnProgress) {
        return new Promise(function (fnResolve, fnReject) {
            const sCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const oFormData = new FormData();
            oFormData.append('file', oBlobInfo.blob(), oBlobInfo.filename());

            fetch('/tinymce/upload', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': sCsrf },
                body: oFormData,
            })
            .then(function (oResponse) {
                if (!oResponse.ok) { fnReject('HTTP ' + oResponse.status); return; }
                return oResponse.json();
            })
            .then(function (oJson) {
                if (oJson && oJson.location) {
                    fnResolve(oJson.location);
                } else {
                    fnReject('Réponse invalide du serveur');
                }
            })
            .catch(function (oErr) { fnReject(oErr.message); });
        });
    },
    setup: function (oEditor) {
        oEditor.on('change', function () { oEditor.save(); });
    }
});
</script>
@endpush
