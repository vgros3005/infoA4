@extends('layouts.app')

@section('title', __('Équipes'))
@section('page-title', __('Gestion des équipes'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Administration') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Équipes') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $oTeams->total() }} {{ __('équipe(s)') }}</span>
    <a href="{{ route('admin.teams.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle équipe') }}
    </a>
</div>

<div class="row g-3">
    @forelse($oTeams as $oTeam)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title mb-1">
                            <i class="bi bi-diagram-3 me-2 text-primary"></i>{{ $oTeam->name }}
                        </h5>
                        @if($oTeam->description)
                            <p class="text-muted small mb-0">{{ Str::limit($oTeam->description, 80) }}</p>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.teams.edit', $oTeam) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Modifier') }}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#delTeam{{ $oTeam->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">{{ $oTeam->members_count ?? $oTeam->teamUserRoles->count() }} {{ __('membre(s)') }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($oTeam->teamUserRoles->take(6) as $oTUR)
                            <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                {{ $oTUR->user->full_name ?? '?' }}
                                <span class="text-muted">({{ $oTUR->role->name ?? '?' }})</span>
                            </span>
                        @endforeach
                        @if($oTeam->teamUserRoles->count() > 6)
                            <span class="badge bg-light text-muted border">+{{ $oTeam->teamUserRoles->count() - 6 }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.teams.show', $oTeam) }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-people me-1"></i>{{ __('Gérer les membres') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Modal suppression --}}
        <div class="modal fade" id="delTeam{{ $oTeam->id }}" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title text-danger">{{ __('Supprimer l\'équipe ?') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body small">
                        <strong>{{ $oTeam->name }}</strong><br>
                        {{ __('Les membres seront détachés de cette équipe.') }}
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <form method="POST" action="{{ route('admin.teams.destroy', $oTeam) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-diagram-3 d-block fs-2 text-muted mb-2"></i>
                    <p class="text-muted">{{ __('Aucune équipe créée') }}</p>
                    <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Créer la première équipe') }}
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($oTeams->hasPages())
    <div class="mt-3">
        {{ $oTeams->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
