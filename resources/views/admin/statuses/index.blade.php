@extends('layouts.app')

@section('title', __('Statuts & Workflow'))
@section('page-title', __('Statuts & Workflow'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Statuts & Workflow') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0 small">{{ __('Configurez les statuts et les transitions de workflow.') }}</p>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i>{{ __('Nouveau statut') }}
    </button>
</div>

<div class="row g-3">
    @forelse($aStatuses as $oStatus)
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle" style="width: 16px; height: 16px; background-color: var(--bs-{{ $oStatus->color ?? 'secondary' }});"></div>
                            <h5 class="mb-0 fw-bold">
                                {{ $oStatus->translated_label }}
                                <small class="text-muted fw-normal fs-6">({{ $oStatus->name }})</small>
                            </h5>
                            <span class="badge bg-light text-dark border">{{ __('Ordre') }} {{ $oStatus->sort_order ?? '—' }}</span>
                            @if($oStatus->is_final ?? false)
                                <span class="badge bg-success">{{ __('Statut final') }}</span>
                            @endif
                            @if($oStatus->is_locked ?? false)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-lock me-1"></i>{{ __('Figé') }}
                                </span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#statusModal"
                                    data-mode="edit"
                                    data-status-id="{{ $oStatus->id }}"
                                    data-status-name="{{ $oStatus->name }}"
                                    data-status-color="{{ $oStatus->color }}"
                                    data-status-sort-order="{{ $oStatus->sort_order }}"
                                    data-status-is-final="{{ $oStatus->is_final ? '1' : '0' }}"
                                    data-status-is-locked="{{ $oStatus->is_locked ? '1' : '0' }}">
                                <i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    data-bs-toggle="modal" data-bs-target="#actionModal"
                                    data-mode="create"
                                    data-status-id="{{ $oStatus->id }}"
                                    data-status-name="{{ $oStatus->name }}">
                                <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter une action') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Actions de ce statut --}}
                @if($oStatus->actions->isNotEmpty())
                    <div class="card-body pt-0 pb-2">
                        <p class="text-muted small mb-2">{{ __('Actions disponibles depuis ce statut :') }}</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Action') }}</th>
                                        <th>{{ __('Statut cible') }}</th>
                                        <th>{{ __('Rôles autorisés') }}</th>
                                        <th>{{ __('Commentaire') }}</th>
                                        <th class="text-end">{{ __('ui.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($oStatus->actions as $oAction)
                                        <tr>
                                            <td>
                                                <span class="fw-medium">{{ $oAction->action_label }}</span>
                                            </td>
                                            <td>
                                                @if($oAction->targetStatus)
                                                    <span class="badge" style="background-color: var(--bs-{{ $oAction->targetStatus->color ?? 'secondary' }})">
                                                        {{ $oAction->targetStatus->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($oAction->roles ?? [] as $oRole)
                                                    <span class="badge bg-light text-dark border me-1">{{ $oRole->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if($oAction->requires_comment)
                                                    <span class="badge bg-warning text-dark">{{ __('Obligatoire') }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted">{{ __('Optionnel') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary"
                                                            data-bs-toggle="modal" data-bs-target="#actionModal"
                                                            data-mode="edit"
                                                            data-status-id="{{ $oStatus->id }}"
                                                            data-status-name="{{ $oStatus->name }}"
                                                            data-action-id="{{ $oAction->id }}"
                                                            data-action-name="{{ $oAction->action_name }}"
                                                            data-action-label="{{ $oAction->action_label }}"
                                                            data-action-target="{{ $oAction->target_status_id }}"
                                                            data-action-button-color="{{ $oAction->button_color }}"
                                                            data-action-icon="{{ $oAction->icon }}"
                                                            data-action-sort-order="{{ $oAction->sort_order }}"
                                                            data-action-requires-comment="{{ $oAction->requires_comment ? '1' : '0' }}"
                                                            data-action-requires-assignment="{{ $oAction->requires_assignment ? '1' : '0' }}"
                                                            data-action-requires-estimation="{{ $oAction->requires_estimation ? '1' : '0' }}"
                                                            data-action-is-active="{{ $oAction->is_active ? '1' : '0' }}"
                                                            data-action-role-ids="{{ $oAction->roles->pluck('id')->implode(',') }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.status-actions.destroy', [$oStatus->id, $oAction->id]) }}" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger"
                                                                onclick="return confirm('{{ __('Supprimer cette action ?') }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="card-body pt-0 pb-2">
                        <p class="text-muted small fst-italic">
                            <i class="bi bi-info-circle me-1"></i>{{ __('Aucune action définie pour ce statut.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-arrow-repeat d-block fs-2 text-muted mb-2"></i>
                    <p class="text-muted">{{ __('Aucun statut configuré') }}</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

{{-- Modal Statut (create/edit) --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalTitle">{{ __('Statut') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="statusForm" action="{{ route('admin.statuses.store') }}">
                @csrf
                <input type="hidden" name="_method" id="statusMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="statusName" class="form-label fw-medium">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" id="statusName" name="name" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="statusColor" class="form-label fw-medium">{{ __('Couleur') }}</label>
                            <input type="color" id="statusColor" name="color" class="form-control form-control-color" value="#0d6efd">
                        </div>
                        <div class="col-md-6">
                            <label for="statusOrder" class="form-label fw-medium">{{ __('Ordre') }}</label>
                            <input type="number" id="statusOrder" name="sort_order" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="statusIsFinal" name="is_final" value="1">
                            <label class="form-check-label" for="statusIsFinal">{{ __('Statut final') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="statusIsLocked" name="is_locked" value="1">
                            <label class="form-check-label" for="statusIsLocked">{{ __('Fige la demande') }}</label>
                        </div>
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

{{-- Modal Action (create/edit) --}}
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle">
                    <i class="bi bi-plus-circle me-2"></i><span id="actionModalStatusName"></span> — {{ __('Nouvelle action') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="actionForm" action="#" data-base-url="{{ url('admin/statuses') }}">
                @csrf
                <input type="hidden" name="_method" id="actionMethod" value="POST">
                <input type="hidden" name="status_id" id="actionStatusId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="actionName" class="form-label fw-medium">
                                {{ __('Nom technique') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="actionName" name="action_name" class="form-control"
                                   placeholder="{{ __('Ex : validate, reject…') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="actionLabel" class="form-label fw-medium">
                                {{ __('Libellé de l\'action') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="actionLabel" name="action_label" class="form-control"
                                   placeholder="{{ __('Ex : Valider, Rejeter, Clôturer…') }}" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="actionTargetStatus" class="form-label fw-medium">
                            {{ __('Statut cible') }} <span class="text-danger">*</span>
                        </label>
                        <select id="actionTargetStatus" name="target_status_id" class="form-select" required>
                            <option value="">{{ __('Sélectionner…') }}</option>
                            @foreach($aStatuses as $oS)
                                <option value="{{ $oS->id }}">{{ $oS->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="actionButtonColor" class="form-label fw-medium">{{ __('Couleur du bouton') }}</label>
                            <select id="actionButtonColor" name="button_color" class="form-select">
                                @foreach(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark', 'indigo', 'teal'] as $sColor)
                                    <option value="{{ $sColor }}">{{ $sColor }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="actionIcon" class="form-label fw-medium">{{ __('Icône') }}</label>
                            <input type="text" id="actionIcon" name="icon" class="form-control" placeholder="bi-check-circle">
                        </div>
                        <div class="col-md-4">
                            <label for="actionSortOrder" class="form-label fw-medium">{{ __('Ordre') }}</label>
                            <input type="number" id="actionSortOrder" name="sort_order" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label fw-medium">{{ __('Rôles autorisés') }}</label>
                        <div class="border rounded p-2">
                            @foreach($aRoles ?? [] as $oRole)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="role_ids[]" value="{{ $oRole->id }}"
                                           id="actionRole{{ $oRole->id }}">
                                    <label class="form-check-label" for="actionRole{{ $oRole->id }}">
                                        {{ $oRole->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="actionRequiresComment"
                                   name="requires_comment" value="1">
                            <label class="form-check-label fw-medium" for="actionRequiresComment">
                                {{ __('Commentaire obligatoire') }}
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="actionRequiresAssignment"
                                   name="requires_assignment" value="1">
                            <label class="form-check-label fw-medium" for="actionRequiresAssignment">
                                {{ __('Affectation requise') }}
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="actionRequiresEstimation"
                                   name="requires_estimation" value="1">
                            <label class="form-check-label fw-medium" for="actionRequiresEstimation">
                                {{ __('Chiffrage requis') }}
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="actionIsActive"
                                   name="is_active" value="1" checked>
                            <label class="form-check-label fw-medium" for="actionIsActive">
                                {{ __('Actif') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-success" id="actionSubmitBtn">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter l\'action') }}
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
    // Modal statut
    const elStatusModal = document.getElementById('statusModal');
    if (elStatusModal) {
        elStatusModal.addEventListener('show.bs.modal', function (oEvent) {
            const elTrigger = oEvent.relatedTarget;
            const sMode = elTrigger.dataset.mode;

            document.getElementById('statusModalTitle').textContent =
                sMode === 'edit' ? '{{ __("Modifier le statut") }}' : '{{ __("Nouveau statut") }}';

            if (sMode === 'edit') {
                const sId = elTrigger.dataset.statusId;
                document.getElementById('statusForm').action = '/admin/statuses/' + sId;
                document.getElementById('statusMethod').value = 'PUT';
                document.getElementById('statusName').value = elTrigger.dataset.statusName;
                document.getElementById('statusColor').value = elTrigger.dataset.statusColor || '#0d6efd';
                document.getElementById('statusOrder').value = elTrigger.dataset.statusSortOrder || 0;
                document.getElementById('statusIsFinal').checked = elTrigger.dataset.statusIsFinal === '1';
                document.getElementById('statusIsLocked').checked = elTrigger.dataset.statusIsLocked === '1';
            } else {
                document.getElementById('statusForm').action = '{{ route("admin.statuses.store") }}';
                document.getElementById('statusMethod').value = 'POST';
                document.getElementById('statusName').value = '';
                document.getElementById('statusColor').value = '#0d6efd';
                document.getElementById('statusOrder').value = 0;
                document.getElementById('statusIsFinal').checked = false;
                document.getElementById('statusIsLocked').checked = false;
            }
        });
    }

    // Modal action
    const elActionModal = document.getElementById('actionModal');
    if (elActionModal) {
        elActionModal.addEventListener('show.bs.modal', function (oEvent) {
            const elTrigger = oEvent.relatedTarget;
            const sMode = elTrigger.dataset.mode;
            const sStatusId = elTrigger.dataset.statusId;

            document.getElementById('actionStatusId').value = sStatusId;
            document.getElementById('actionModalStatusName').textContent = elTrigger.dataset.statusName;
            document.getElementById('actionModalTitle').innerHTML =
                '<i class="bi bi-' + (sMode === 'edit' ? 'pencil' : 'plus-circle') + ' me-2"></i>'
                + elTrigger.dataset.statusName + ' — '
                + (sMode === 'edit' ? '{{ __("Modifier l\'action") }}' : '{{ __("Nouvelle action") }}');
            document.getElementById('actionSubmitBtn').innerHTML =
                '<i class="bi bi-' + (sMode === 'edit' ? 'check-circle' : 'plus-circle') + ' me-1"></i>'
                + (sMode === 'edit' ? '{{ __("Enregistrer les modifications") }}' : '{{ __("Ajouter l\'action") }}');

            const oForm = document.getElementById('actionForm');
            const aRoleIds = (elTrigger.dataset.actionRoleIds || '').split(',').filter(Boolean);
            document.querySelectorAll('input[name="role_ids[]"]').forEach(function (elCheckbox) {
                elCheckbox.checked = aRoleIds.includes(elCheckbox.value);
            });

            if (sMode === 'edit') {
                const sActionId = elTrigger.dataset.actionId;
                oForm.action = oForm.dataset.baseUrl + '/' + sStatusId + '/actions/' + sActionId;
                document.getElementById('actionMethod').value = 'PUT';
                document.getElementById('actionName').value = elTrigger.dataset.actionName || '';
                document.getElementById('actionLabel').value = elTrigger.dataset.actionLabel || '';
                document.getElementById('actionTargetStatus').value = elTrigger.dataset.actionTarget || '';
                document.getElementById('actionButtonColor').value = elTrigger.dataset.actionButtonColor || 'primary';
                document.getElementById('actionIcon').value = elTrigger.dataset.actionIcon || '';
                document.getElementById('actionSortOrder').value = elTrigger.dataset.actionSortOrder || 0;
                document.getElementById('actionRequiresComment').checked = elTrigger.dataset.actionRequiresComment === '1';
                document.getElementById('actionRequiresAssignment').checked = elTrigger.dataset.actionRequiresAssignment === '1';
                document.getElementById('actionRequiresEstimation').checked = elTrigger.dataset.actionRequiresEstimation === '1';
                document.getElementById('actionIsActive').checked = elTrigger.dataset.actionIsActive === '1';
            } else {
                oForm.action = oForm.dataset.baseUrl + '/' + sStatusId + '/actions';
                document.getElementById('actionMethod').value = 'POST';
                document.getElementById('actionName').value = '';
                document.getElementById('actionLabel').value = '';
                document.getElementById('actionTargetStatus').value = '';
                document.getElementById('actionButtonColor').value = 'primary';
                document.getElementById('actionIcon').value = '';
                document.getElementById('actionSortOrder').value = 0;
                document.getElementById('actionRequiresComment').checked = false;
                document.getElementById('actionRequiresAssignment').checked = false;
                document.getElementById('actionRequiresEstimation').checked = false;
                document.getElementById('actionIsActive').checked = true;
            }
        });
    }
})();
</script>
@endpush
