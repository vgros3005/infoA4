@extends('layouts.app')

@section('title', __('Sociétés'))
@section('page-title', __('Gestion des sociétés'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Sociétés') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $aCompanies->count() }} {{ __('société(s)') }}</span>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#companyModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle société') }}
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">{{ __('Nom') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Demandes') }}</th>
                        <th class="text-end pe-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aCompanies as $oCompany)
                        <tr>
                            <td class="ps-3 fw-medium">
                                <i class="bi bi-building me-2 text-muted"></i>{{ $oCompany->name }}
                            </td>
                            <td><code class="small">{{ $oCompany->code ?? '—' }}</code></td>
                            <td><span class="badge bg-secondary">{{ $oCompany->requests_count ?? 0 }}</span></td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#companyModal"
                                            data-mode="edit"
                                            data-company-id="{{ $oCompany->id }}"
                                            data-company-name="{{ $oCompany->name }}"
                                            data-company-code="{{ $oCompany->code }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#delCompany{{ $oCompany->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="delCompany{{ $oCompany->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title text-danger">{{ __('Supprimer ?') }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body small"><strong>{{ $oCompany->name }}</strong></div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                        <form method="POST" action="{{ route('admin.companies.destroy', $oCompany) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-building d-block fs-2 mb-2"></i>
                                {{ __('Aucune société') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="companyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companyModalTitle">{{ __('Société') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="companyForm" action="{{ route('admin.companies.store') }}">
                @csrf
                <input type="hidden" name="_method" id="companyMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="companyName" class="form-label fw-medium">
                            {{ __('Nom') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="companyName" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="companyCode" class="form-label fw-medium">{{ __('Code / Abréviation') }}</label>
                        <input type="text" id="companyCode" name="code" class="form-control"
                               placeholder="{{ __('Ex: CORP, DSI, SA…') }}" maxlength="10">
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
    const elModal = document.getElementById('companyModal');
    if (!elModal) return;
    elModal.addEventListener('show.bs.modal', function (oEvent) {
        const elTrigger = oEvent.relatedTarget;
        const sMode = elTrigger.dataset.mode;
        document.getElementById('companyModalTitle').textContent =
            sMode === 'edit' ? '{{ __("Modifier la société") }}' : '{{ __("Nouvelle société") }}';
        if (sMode === 'edit') {
            document.getElementById('companyForm').action = '/admin/companies/' + elTrigger.dataset.companyId;
            document.getElementById('companyMethod').value = 'PUT';
            document.getElementById('companyName').value = elTrigger.dataset.companyName;
            document.getElementById('companyCode').value = elTrigger.dataset.companyCode || '';
        } else {
            document.getElementById('companyForm').action = '{{ route("admin.companies.store") }}';
            document.getElementById('companyMethod').value = 'POST';
            document.getElementById('companyName').value = '';
            document.getElementById('companyCode').value = '';
        }
    });
})();
</script>
@endpush
