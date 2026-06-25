@extends('layouts.app')

@section('title', __('Types de demande'))
@section('page-title', __('Types de demande'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Types de demande') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $aRequestTypes->count() }} {{ __('type(s)') }}</span>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#typeModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i>{{ __('Nouveau type') }}
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Nom') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Demandes associées') }}</th>
                        <th>{{ __('Actif') }}</th>
                        <th class="text-end pe-3">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aRequestTypes as $oType)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $oType->name }}</td>
                            <td><span class="text-muted small">{{ Str::limit($oType->description, 60) ?? '—' }}</span></td>
                            <td>
                                <span class="badge bg-secondary">{{ $oType->requests_count ?? $oType->requestsA4->count() }}</span>
                            </td>
                            <td>
                                @if($oType->is_active ?? true)
                                    <span class="badge bg-success">{{ __('Oui') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Non') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#typeModal"
                                            data-mode="edit"
                                            data-type-id="{{ $oType->id }}"
                                            data-type-name="{{ $oType->name }}"
                                            data-type-description="{{ $oType->description }}"
                                            data-type-active="{{ ($oType->is_active ?? true) ? '1' : '0' }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#delType{{ $oType->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="delType{{ $oType->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title text-danger">{{ __('Supprimer ?') }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body small">
                                        <strong>{{ $oType->name }}</strong>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                        <form method="POST" action="{{ route('admin.request-types.destroy', $oType) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-tags d-block fs-2 mb-2"></i>
                                {{ __('Aucun type de demande') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal create/edit inline --}}
<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="typeModalTitle">{{ __('Type de demande') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="typeForm" action="{{ route('admin.request-types.store') }}">
                @csrf
                <input type="hidden" name="_method" id="typeMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="typeName" class="form-label fw-medium">
                            {{ __('Nom') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="typeName" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="typeDescription" class="form-label fw-medium">{{ __('Description') }}</label>
                        <textarea id="typeDescription" name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="typeActive" name="is_active" value="1" checked>
                        <label class="form-check-label" for="typeActive">{{ __('Actif') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary" id="typeSubmitBtn">
                        <i class="bi bi-save me-1"></i>{{ __('Enregistrer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const elModal = document.getElementById('typeModal');
    if (!elModal) return;

    elModal.addEventListener('show.bs.modal', function (oEvent) {
        const elTrigger = oEvent.relatedTarget;
        const sMode = elTrigger.dataset.mode;

        document.getElementById('typeModalTitle').textContent =
            sMode === 'edit' ? '{{ __("Modifier le type") }}' : '{{ __("Nouveau type") }}';

        if (sMode === 'edit') {
            const sId = elTrigger.dataset.typeId;
            document.getElementById('typeForm').action = '/admin/request-types/' + sId;
            document.getElementById('typeMethod').value = 'PUT';
            document.getElementById('typeName').value = elTrigger.dataset.typeName;
            document.getElementById('typeDescription').value = elTrigger.dataset.typeDescription || '';
            document.getElementById('typeActive').checked = elTrigger.dataset.typeActive === '1';
        } else {
            document.getElementById('typeForm').action = '{{ route("admin.request-types.store") }}';
            document.getElementById('typeMethod').value = 'POST';
            document.getElementById('typeName').value = '';
            document.getElementById('typeDescription').value = '';
            document.getElementById('typeActive').checked = true;
        }
    });
})();
</script>
@endpush
