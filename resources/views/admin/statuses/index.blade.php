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
                            <h5 class="mb-0 fw-bold">{{ $oStatus->name }}</h5>
                            <span class="badge bg-light text-dark border">{{ __('Ordre') }} {{ $oStatus->order ?? '—' }}</span>
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
                                    data-status-order="{{ $oStatus->order }}"
                                    data-status-is-final="{{ $oStatus->is_final ? '1' : '0' }}"
                                    data-status-is-locked="{{ $oStatus->is_locked ? '1' : '0' }}">
                                <i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    data-bs-toggle="modal" data-bs-target="#actionModal"
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
                                                <span class="fw-medium">{{ $oAction->label }}</span>
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
                                                @foreach($oAction->allowedRoles ?? [] as $oRole)
                                                    <span class="badge bg-light text-dark border me-1">{{ $oRole->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if($oAction->comment_required)
                                                    <span class="badge bg-warning text-dark">{{ __('Obligatoire') }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted">{{ __('Optionnel') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary btn-edit-action"
                                                            data-action-id="{{ $oAction->id }}"
                                                            data-action-label="{{ $oAction->label }}"
                                                            data-action-target="{{ $oAction->target_status_id }}"
                                                            data-action-comment="{{ $oAction->comment_required ? '1' : '0' }}"
                                                            data-status-id="{{ $oStatus->id }}">
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
                            <input type="number" id="statusOrder" name="order" class="form-control" min="0" value="0">
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

{{-- Modal Ajout action --}}
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i><span id="actionModalStatusName"></span> — {{ __('Nouvelle action') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="actionForm" action="#" data-base-url="{{ url('admin/statuses') }}">
                @csrf
                <input type="hidden" name="status_id" id="actionStatusId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="actionLabel" class="form-label fw-medium">
                            {{ __('Libellé de l\'action') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="actionLabel" name="label" class="form-control"
                               placeholder="{{ __('Ex : Valider, Rejeter, Clôturer…') }}" required>
                    </div>
                    <div class="mb-3">
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
                    <div class="mb-3">
                        <label class="form-label fw-medium">{{ __('Rôles autorisés') }}</label>
                        <div class="border rounded p-2">
                            @foreach($aRoles ?? [] as $oRole)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="allowed_role_ids[]" value="{{ $oRole->id }}"
                                           id="actionRole{{ $oRole->id }}">
                                    <label class="form-check-label" for="actionRole{{ $oRole->id }}">
                                        {{ $oRole->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="actionCommentRequired"
                               name="comment_required" value="1">
                        <label class="form-check-label fw-medium" for="actionCommentRequired">
                            {{ __('Commentaire obligatoire') }}
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-success">
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
                document.getElementById('statusOrder').value = elTrigger.dataset.statusOrder || 0;
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
            const sStatusId = elTrigger.dataset.statusId;
            document.getElementById('actionStatusId').value = sStatusId;
            document.getElementById('actionModalStatusName').textContent = elTrigger.dataset.statusName;
            const oForm = document.getElementById('actionForm');
            oForm.action = oForm.dataset.baseUrl + '/' + sStatusId + '/actions';
        });
    }
})();
</script>
@endpush
