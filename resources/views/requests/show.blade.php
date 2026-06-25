@extends('layouts.app')

@section('title', $oRequest->reference . ' — ' . Str::limit($oRequest->title, 40))
@section('page-title', $oRequest->reference)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('requests.index') }}">{{ __('Fiches A4') }}</a></li>
    <li class="breadcrumb-item active">{{ $oRequest->reference }}</li>
@endsection

@section('content')
{{-- En-tête fiche --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="mb-1 fw-bold">{{ $oRequest->title }}</h4>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill" style="background-color: {{ $oRequest->status->color ?? '#6c757d' }}; font-size: 0.85rem;">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                        {{ $oRequest->status->translated_label ?? '—' }}
                    </span>
                    <span class="badge rounded-pill" style="background-color: {{ $oRequest->priority->color ?? '#adb5bd' }}">
                        {{ $oRequest->priority->name ?? '—' }}
                    </span>
                    <span class="badge bg-light text-dark border">{{ $oRequest->requestType->name ?? '—' }}</span>
                    <small class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i>{{ __('Souhaitée le') }}
                        @if($oRequest->desired_date)
                            <strong class="{{ $oRequest->desired_date->isPast() ? 'text-danger' : '' }}">
                                {{ $oRequest->desired_date->format('d/m/Y') }}
                            </strong>
                        @else
                            —
                        @endif
                    </small>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                {{-- Boutons d'action workflow --}}
                @foreach($aAvailableActions as $oAction)
                    <button type="button" class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#actionModal"
                            data-action-id="{{ $oAction->id }}"
                            data-action-label="{{ $oAction->translated_label }}"
                            data-comment-required="{{ $oAction->comment_required ? '1' : '0' }}"
                            data-target-status="{{ $oAction->targetStatus->translated_label ?? '' }}">
                        <i class="bi bi-arrow-right-circle me-1"></i>{{ $oAction->translated_label }}
                    </button>
                @endforeach

                @can('update', $oRequest)
                    <a href="{{ route('requests.edit', $oRequest) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}
                    </a>
                @endcan

                <a href="{{ route('requests.pdf', $oRequest) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                    <i class="bi bi-file-pdf me-1"></i>PDF
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Onglets --}}
<ul class="nav nav-tabs mb-3" id="requestTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-detail" data-bs-toggle="tab" data-bs-target="#pane-detail"
                type="button" role="tab">
            <i class="bi bi-info-circle me-1"></i>{{ __('Détail') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-history" data-bs-toggle="tab" data-bs-target="#pane-history"
                type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i>{{ __('Historique') }}
            <span class="badge bg-secondary ms-1">{{ count($aStatusHistory) }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-tasks" data-bs-toggle="tab" data-bs-target="#pane-tasks"
                type="button" role="tab">
            <i class="bi bi-check2-square me-1"></i>{{ __('Tâches') }}
            <span class="badge bg-secondary ms-1">{{ $oRequest->tasks->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-attachments" data-bs-toggle="tab" data-bs-target="#pane-attachments"
                type="button" role="tab">
            <i class="bi bi-paperclip me-1"></i>{{ __('Pièces jointes') }}
            <span class="badge bg-secondary ms-1">{{ $oRequest->attachments->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-pdf" data-bs-toggle="tab" data-bs-target="#pane-pdf"
                type="button" role="tab">
            <i class="bi bi-file-pdf me-1"></i>{{ __('PDF') }}
        </button>
    </li>
</ul>

<div class="tab-content" id="requestTabsContent">
    {{-- Tab Détail --}}
    <div class="tab-pane fade show active" id="pane-detail" role="tabpanel">
        <div class="row g-3">
            <div class="col-12 col-lg-8">
                {{-- Description --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-text-paragraph me-2"></i>{{ __('Description') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $oRequest->description }}</p>
                    </div>
                </div>

                {{-- Contenu WYSIWYG --}}
                @if($oRequest->content)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-file-richtext me-2"></i>{{ __('Contenu détaillé') }}</h6>
                    </div>
                    <div class="card-body wysiwyg-content">
                        {!! $oRequest->content !!}
                    </div>
                </div>
                @endif

                {{-- Notes internes --}}
                @can('seeInternalNotes', $oRequest)
                    @if($oRequest->internal_notes)
                    <div class="card border-0 shadow-sm border-start border-warning border-3">
                        <div class="card-header bg-transparent border-0">
                            <h6 class="mb-0 fw-bold text-warning">
                                <i class="bi bi-lock me-2"></i>{{ __('Notes internes') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $oRequest->internal_notes }}</p>
                        </div>
                    </div>
                    @endif
                @endcan
            </div>

            {{-- Métadonnées --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-square me-2"></i>{{ __('Métadonnées') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <dl class="mb-0">
                            <div class="d-flex border-bottom px-3 py-2">
                                <dt class="text-muted small fw-normal" style="min-width: 130px;">{{ __('Demandeur') }}</dt>
                                <dd class="mb-0 small fw-medium">{{ $oRequest->requester->full_name ?? '—' }}</dd>
                            </div>
                            <div class="d-flex border-bottom px-3 py-2">
                                <dt class="text-muted small fw-normal" style="min-width: 130px;">{{ __('Date de demande') }}</dt>
                                <dd class="mb-0 small">{{ $oRequest->requested_date?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div class="d-flex border-bottom px-3 py-2">
                                <dt class="text-muted small fw-normal" style="min-width: 130px;">{{ __('Date souhaitée') }}</dt>
                                <dd class="mb-0 small {{ $oRequest->desired_date?->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ $oRequest->desired_date?->format('d/m/Y') ?? '—' }}
                                </dd>
                            </div>
                            <div class="d-flex border-bottom px-3 py-2">
                                <dt class="text-muted small fw-normal" style="min-width: 130px;">{{ __('Société(s)') }}</dt>
                                <dd class="mb-0 small">
                                    @foreach($oRequest->companies as $oCompany)
                                        <span class="badge bg-light text-dark border me-1">{{ $oCompany->name }}</span>
                                    @endforeach
                                </dd>
                            </div>
                            <div class="d-flex border-bottom px-3 py-2">
                                <dt class="text-muted small fw-normal" style="min-width: 130px;">{{ __('Logiciel(s)') }}</dt>
                                <dd class="mb-0 small">
                                    @foreach($oRequest->softwares as $oSoftware)
                                        <span class="badge bg-light text-dark border me-1">{{ $oSoftware->name }}</span>
                                    @endforeach
                                </dd>
                            </div>
                            @if($oRequest->priority_justification)
                            <div class="d-flex px-3 py-2">
                                <dt class="text-muted small fw-normal" style="min-width: 130px;">{{ __('Justification') }}</dt>
                                <dd class="mb-0 small">{{ $oRequest->priority_justification }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Historique --}}
    <div class="tab-pane fade" id="pane-history" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @forelse($aStatusHistory as $oHistory)
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 36px; height: 36px; background-color: {{ $oHistory->newStatus->color ?? '#6c757d' }}20; border: 2px solid {{ $oHistory->newStatus->color ?? '#6c757d' }};">
                                <i class="bi bi-arrow-right small" style="color: {{ $oHistory->newStatus->color ?? '#6c757d' }};"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-medium small">{{ $oHistory->user->full_name ?? '—' }}</span>
                                    <span class="text-muted small mx-2">→</span>
                                    <span class="badge" style="background-color: {{ $oHistory->newStatus->color ?? '#6c757d' }}">
                                        {{ $oHistory->newStatus->translated_label ?? '—' }}
                                    </span>
                                    @if($oHistory->oldStatus)
                                        <span class="text-muted small ms-2">
                                            ({{ __('ui.from_status') }} {{ $oHistory->oldStatus->translated_label }})
                                        </span>
                                    @endif
                                </div>
                                <small class="text-muted text-nowrap ms-2">
                                    {{ $oHistory->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            @if($oHistory->comment)
                                <p class="text-muted small mt-1 mb-0 fst-italic">"{{ $oHistory->comment }}"</p>
                            @endif
                        </div>
                    </div>
                    @if(!$loop->last)
                        <hr class="my-2 ms-5">
                    @endif
                @empty
                    <p class="text-muted text-center py-4">{{ __('Aucun historique disponible') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tab Tâches --}}
    <div class="tab-pane fade" id="pane-tasks" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ __('Tâches liées') }}</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('tasks.gantt', ['request_id' => $oRequest->id]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-bar-chart-gantt me-1"></i>{{ __('Voir Gantt') }}
                </a>
                @can('create', \App\Models\Task::class)
                    <a href="{{ route('tasks.create', ['request_a4_id' => $oRequest->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter une tâche') }}
                    </a>
                @endcan
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">{{ __('Titre') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Assigné à') }}</th>
                            <th>{{ __('Estimation') }}</th>
                            <th>{{ __('Réel') }}</th>
                            <th>{{ __('Avancement') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($oRequest->tasks as $oTask)
                            <tr>
                                <td class="ps-3 fw-medium small">{{ $oTask->title }}</td>
                                <td><span class="text-muted small">{{ $oTask->taskType->name ?? '—' }}</span></td>
                                <td><span class="small">{{ $oTask->assignee->full_name ?? '—' }}</span></td>
                                <td><span class="badge bg-light text-dark border">{{ $oTask->estimated_hours }}h</span></td>
                                <td><span class="badge bg-light text-dark border">{{ $oTask->actual_hours ?? 0 }}h</span></td>
                                <td style="min-width: 100px;">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $oTask->progress }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $oTask->progress }}%</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ __('task_status.' . ($oTask->status ?? 'pending')) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('tasks.show', $oTask) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    {{ __('Aucune tâche pour cette demande') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab Pièces jointes --}}
    <div class="tab-pane fade" id="pane-attachments" role="tabpanel">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0">{{ __('Ajouter une pièce jointe') }}</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('requests.attachments.store', $oRequest) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex gap-2">
                        <input type="file" name="attachment" class="form-control form-control-sm" required>
                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                            <i class="bi bi-upload me-1"></i>{{ __('Envoyer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2">
            @forelse($oRequest->attachments as $oAttachment)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center py-2">
                            <i class="bi bi-paperclip fs-4 text-muted me-3"></i>
                            <div class="flex-grow-1 min-width-0">
                                <p class="mb-0 text-truncate small fw-medium">{{ $oAttachment->original_name }}</p>
                                <small class="text-muted">{{ number_format($oAttachment->size / 1024, 1) }} KB</small>
                            </div>
                            <div class="d-flex gap-1 ms-2">
                                <a href="{{ route('attachments.download', $oAttachment) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Télécharger') }}">
                                    <i class="bi bi-download"></i>
                                </a>
                                @can('delete', $oAttachment)
                                <form method="POST" action="{{ route('attachments.destroy', $oAttachment) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted text-center py-4">{{ __('Aucune pièce jointe') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Tab PDF --}}
    <div class="tab-pane fade" id="pane-pdf" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ __('Versions PDF') }}</h6>
            @can('generatePdf', $oRequest)
                <form method="POST" action="{{ route('requests.pdf.generate', $oRequest) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-file-pdf me-1"></i>{{ __('Générer une version PDF') }}
                    </button>
                </form>
            @endcan
        </div>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">{{ __('Version') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Généré par') }}</th>
                            <th>{{ __('Statut au moment') }}</th>
                            <th class="text-end pe-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($oRequest->pdfVersions ?? [] as $oPdf)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-danger">v{{ $oPdf->pdf_version_number }}</span>
                                </td>
                                <td><small>{{ $oPdf->created_at->format('d/m/Y H:i') }}</small></td>
                                <td><small>{{ $oPdf->uploader->full_name ?? '—' }}</small></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $oPdf->description ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('requests.pdf.download', [$oRequest, $oPdf]) }}"
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-download me-1"></i>{{ __('Télécharger') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ __('Aucune version PDF générée') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal action workflow --}}
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle">
                    <i class="bi bi-arrow-right-circle me-2 text-primary"></i>
                    <span id="actionModalLabel">{{ __('Action') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="actionForm" action="">
                @csrf
                <input type="hidden" name="action_id" id="actionId">
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="actionDescription"></p>
                    <div>
                        <label for="actionComment" class="form-label fw-medium">
                            {{ __('Commentaire') }}
                            <span id="commentRequiredMark" class="text-danger" style="display: none;">*</span>
                        </label>
                        <textarea id="actionComment" name="comment" rows="4"
                                  class="form-control"
                                  placeholder="{{ __('Commentaire sur ce changement de statut…') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('Annuler') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="actionSubmitBtn">
                        <i class="bi bi-check-circle me-1"></i>
                        <span id="actionSubmitLabel">{{ __('Confirmer') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Gestion du modal d'action workflow
(function () {
    const elModal = document.getElementById('actionModal');
    if (!elModal) return;

    elModal.addEventListener('show.bs.modal', function (oEvent) {
        const elTrigger = oEvent.relatedTarget;
        const sActionId = elTrigger.dataset.actionId;
        const sActionLabel = elTrigger.dataset.actionLabel;
        const bCommentRequired = elTrigger.dataset.commentRequired === '1';
        const sTargetStatus = elTrigger.dataset.targetStatus;

        document.getElementById('actionModalLabel').textContent = sActionLabel;
        document.getElementById('actionId').value = sActionId;
        document.getElementById('actionSubmitLabel').textContent = sActionLabel;
        document.getElementById('commentRequiredMark').style.display = bCommentRequired ? 'inline' : 'none';

        const elComment = document.getElementById('actionComment');
        elComment.required = bCommentRequired;

        if (sTargetStatus) {
            document.getElementById('actionDescription').textContent =
                '{{ __("Cette action changera le statut vers") }} : ' + sTargetStatus;
        }

        const elForm = document.getElementById('actionForm');
        elForm.action = '{{ url("requests/" . $oRequest->id . "/actions") }}/' + sActionId;
    });
})();
</script>
@endpush
