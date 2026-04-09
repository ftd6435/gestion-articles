<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Types d'opérations</h1>
            <p class="text-muted mb-0">Configurer les types (sortie/entrée) pour les opérations diverses</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i> Nouveau type
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="p-3">
                @include('components.shared.alerts')
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Nature</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td class="fw-semibold">{{ $type->name }}</td>
                                <td>
                                    @if ((int) $type->nature === 1)
                                        <span class="badge bg-success">Entrée</span>
                                    @else
                                        <span class="badge bg-danger">Sortie</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $type->description ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" wire:click="edit({{ $type->id }})">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <button class="btn btn-outline-danger"
                                            wire:click="deleteConfirm({{ $type->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fa fa-inbox fa-2x mb-3 opacity-25"></i>
                                    <p class="mb-0">Aucun type d'opération</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($showModal)
        <div wire:show="showModal" class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered bg-white">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white px-4 py-2">
                        <h5 class="modal-title">{{ $typeId ? 'Modifier le type' : 'Nouveau type' }}</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                wire:model.defer="name" placeholder="Ex: Achat mobilier">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nature <span class="text-danger">*</span></label>
                            <select class="form-select @error('nature') is-invalid @enderror" wire:model.defer="nature">
                                <option value="0">Sortie</option>
                                <option value="1">Entrée</option>
                            </select>
                            @error('nature')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror"
                                wire:model.defer="description" placeholder="Optionnel">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer px-4 py-2">
                        <button class="btn btn-light" type="button" wire:click="closeModal">Annuler</button>
                        <button class="btn btn-primary" type="button" wire:click="store">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
