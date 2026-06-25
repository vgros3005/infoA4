@extends('layouts.app')

@section('title', __('Logiciels'))
@section('page-title', __('Gestion des logiciels'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Logiciels') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $aSoftwares->count() }} {{ __('logiciel(s)') }}</span>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#softwareModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i>{{ __('Nouveau logiciel') }}
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Nom') }}</th>
                        <th>{{ __('Version') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Demandes') }}</th>
                        <th class="text-end pe-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aSoftwares as $oSoftware)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium">
                                    <i class="bi bi-cpu me-2 text-muted"></i>{{ $oSoftware->name }}
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $oSoftware->version ?? '—' }}</span></td>
                            <td><span class="text-muted small">{{ Str::limit($oSoftware->description, 60) ?? '—' }}</span></td>
                            <td><span class="badge bg-secondary">{{ $oSoftware->requests_count ?? 0 }}</span></td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#softwareModal"
                                            data-mode="edit"
                                            data-software-id="{{ $oSoftware->id }}"
                                            data-software-name="{{ $oSoftware->name }}"
                                            data-software-version="{{ $oSoftware->version }}"
                                            data-software-description="{{ $oSoftware->description }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#delSoftware{{ $oSoftware->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="delSoftware{{ $oSoftware->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title text-danger">{{ __('Supprimer ?') }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body small"><strong>{{ $oSoftware->name }}</strong></div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                        <form method="POST" action="{{ route('admin.softwares.destroy', $oSoftware) }}">
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
                                <i class="bi bi-cpu d-block fs-2 mb-2"></i>
                                {{ __('Aucun logiciel') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="softwareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="softwareModalTitle">{{ __('Logiciel') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="softwareForm" action="{{ route('admin.softwares.store') }}">
                @csrf
                <input type="hidden" name="_method" id="softwareMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="softwareName" class="form-label fw-medium">
                            {{ __('Nom') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="softwareName" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="softwareVersion" class="form-label fw-medium">{{ __('Version') }}</label>
                        <input type="text" id="softwareVersion" name="version" class="form-control"
                               placeholder="{{ __('Ex: 2.4.1, v3…') }}">
                    </div>
                    <div class="mb-0">
                        <label for="softwareDescription" class="form-label fw-medium">{{ __('Description') }}</label>
                        <textarea id="softwareDescription" name="description" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">
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
    const elModal = document.getElementById('softwareModal');
    if (!elModal) return;
    elModal.addEventListener('show.bs.modal', function (oEvent) {
        const elTrigger = oEvent.relatedTarget;
        const sMode = elTrigger.dataset.mode;
        document.getElementById('softwareModalTitle').textContent =
            sMode === 'edit' ? '{{ __("Modifier le logiciel") }}' : '{{ __("Nouveau logiciel") }}';
        if (sMode === 'edit') {
            document.getElementById('softwareForm').action = '/admin/softwares/' + elTrigger.dataset.softwareId;
            document.getElementById('softwareMethod').value = 'PUT';
            document.getElementById('softwareName').value = elTrigger.dataset.softwareName;
            document.getElementById('softwareVersion').value = elTrigger.dataset.softwareVersion || '';
            document.getElementById('softwareDescription').value = elTrigger.dataset.softwareDescription || '';
        } else {
            document.getElementById('softwareForm').action = '{{ route("admin.softwares.store") }}';
            document.getElementById('softwareMethod').value = 'POST';
            document.getElementById('softwareName').value = '';
            document.getElementById('softwareVersion').value = '';
            document.getElementById('softwareDescription').value = '';
        }
    });
})();
</script>
@endpush
