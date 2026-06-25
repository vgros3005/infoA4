@extends('layouts.app')

@section('title', __('Priorités'))
@section('page-title', __('Gestion des priorités'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Priorités') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $aPriorities->count() }} {{ __('priorité(s)') }}</span>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#priorityModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle priorité') }}
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Priorité') }}</th>
                        <th>{{ __('Couleur') }}</th>
                        <th>{{ __('Niveau') }}</th>
                        <th>{{ __('Justification requise') }}</th>
                        <th>{{ __('Demandes') }}</th>
                        <th class="text-end pe-3">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aPriorities as $oPriority)
                        <tr>
                            <td class="ps-3">
                                <span class="badge rounded-pill fs-6 px-3" style="background-color: var(--bs-{{ $oPriority->color ?? 'secondary' }});">
                                    {{ $oPriority->name }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded border" style="width: 20px; height: 20px; background-color: var(--bs-{{ $oPriority->color ?? 'secondary' }});"></div>
                                    <code class="small">{{ $oPriority->color ?? '—' }}</code>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $oPriority->level ?? '—' }}</span></td>
                            <td>
                                @if($oPriority->requires_justification)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ __('Oui') }}
                                    </span>
                                @else
                                    <span class="text-muted small">{{ __('Non') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $oPriority->requests_count ?? 0 }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#priorityModal"
                                            data-mode="edit"
                                            data-priority-id="{{ $oPriority->id }}"
                                            data-priority-name="{{ $oPriority->name }}"
                                            data-priority-color="{{ $oPriority->color }}"
                                            data-priority-level="{{ $oPriority->level }}"
                                            data-priority-requires-justification="{{ $oPriority->requires_justification ? '1' : '0' }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#delPriority{{ $oPriority->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="delPriority{{ $oPriority->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title text-danger">{{ __('Supprimer ?') }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body small"><strong>{{ $oPriority->name }}</strong></div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                        <form method="POST" action="{{ route('admin.priorities.destroy', $oPriority) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-exclamation-circle d-block fs-2 mb-2"></i>
                                {{ __('Aucune priorité définie') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal create/edit --}}
<div class="modal fade" id="priorityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="priorityModalTitle">{{ __('Priorité') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="priorityForm" action="{{ route('admin.priorities.store') }}">
                @csrf
                <input type="hidden" name="_method" id="priorityMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="priorityName" class="form-label fw-medium">
                            {{ __('Nom') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="priorityName" name="name" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="priorityColor" class="form-label fw-medium">{{ __('Couleur') }}</label>
                            <input type="color" id="priorityColor" name="color"
                                   class="form-control form-control-color" value="#dc3545">
                        </div>
                        <div class="col-md-6">
                            <label for="priorityLevel" class="form-label fw-medium">{{ __('Niveau (ordre)') }}</label>
                            <input type="number" id="priorityLevel" name="level"
                                   class="form-control" min="1" value="1">
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="priorityRequiresJustification"
                               name="requires_justification" value="1">
                        <label class="form-check-label fw-medium" for="priorityRequiresJustification">
                            {{ __('Justification obligatoire') }}
                        </label>
                        <div class="form-text">{{ __('Si activé, l\'utilisateur devra justifier le choix de cette priorité.') }}</div>
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
    const elModal = document.getElementById('priorityModal');
    if (!elModal) return;

    elModal.addEventListener('show.bs.modal', function (oEvent) {
        const elTrigger = oEvent.relatedTarget;
        const sMode = elTrigger.dataset.mode;

        document.getElementById('priorityModalTitle').textContent =
            sMode === 'edit' ? '{{ __("Modifier la priorité") }}' : '{{ __("Nouvelle priorité") }}';

        if (sMode === 'edit') {
            const sId = elTrigger.dataset.priorityId;
            document.getElementById('priorityForm').action = '/admin/priorities/' + sId;
            document.getElementById('priorityMethod').value = 'PUT';
            document.getElementById('priorityName').value = elTrigger.dataset.priorityName;
            document.getElementById('priorityColor').value = elTrigger.dataset.priorityColor || '#dc3545';
            document.getElementById('priorityLevel').value = elTrigger.dataset.priorityLevel || 1;
            document.getElementById('priorityRequiresJustification').checked =
                elTrigger.dataset.priorityRequiresJustification === '1';
        } else {
            document.getElementById('priorityForm').action = '{{ route("admin.priorities.store") }}';
            document.getElementById('priorityMethod').value = 'POST';
            document.getElementById('priorityName').value = '';
            document.getElementById('priorityColor').value = '#dc3545';
            document.getElementById('priorityLevel').value = 1;
            document.getElementById('priorityRequiresJustification').checked = false;
        }
    });
})();
</script>
@endpush
