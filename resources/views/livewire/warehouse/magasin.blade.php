<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Gestion des magasins</h1>
            <p class="text-muted mb-0">Créer et gérer vos magasins</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i> Nouveau magasin
        </button>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Liste des magasins</h5>
            <span class="badge bg-primary">{{ count($magasins) }} magasin(s)</span>
        </div>

        <div class="card-body p-0">
            @include('components.shared.alerts')

            <!-- Desktop -->
            <div class="table-responsive d-none d-md-block">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Etagère</th>
                            <th>Localisation</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($magasins as $magasin)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $magasin->code_magasin }}</td>
                                <td>{{ $magasin->nom }}</td>
                                <td>{{ count($magasin->etageres) }}</td>
                                <td class="text-muted">{{ $magasin->localisation ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $magasin->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $magasin->status ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button wire:click="edit({{ $magasin->id }})"
                                                class="btn btn-outline-primary"
                                                data-bs-toggle="tooltip"
                                                title="Modifier">
                                            <i class="fa fa-pen"></i>
                                        </button>

                                        <button wire:click="toggleStatus({{ $magasin->id }})"
                                                class="btn btn-outline-{{ $magasin->status ? 'success' : 'secondary' }}"
                                                data-bs-toggle="tooltip"
                                                title="Changer le status">
                                            <i class="fa fa-toggle-{{ $magasin->status ? 'on' : 'off' }}"></i>
                                        </button>

                                        <button wire:click="deleteConfirm({{ $magasin->id }})"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="tooltip"
                                                title="Supprimer">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    Aucun magasin trouvé
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile -->
            <div class="d-md-none">
                @foreach($magasins as $magasin)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold">{{ $magasin->nom }}</h6>
                                <p class="text-muted small mb-1">{{ $magasin->code_magasin }}</p>
                                <span class="badge {{ $magasin->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $magasin->status ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button wire:click="edit({{ $magasin->id }})"
                                    class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fa fa-pen me-1"></i> Modifier
                            </button>

                            <button wire:click="toggleStatus({{ $magasin->id }})"
                                    class="btn btn-sm btn-outline-{{ $magasin->status ? 'success' : 'secondary' }}">
                                <i class="fa fa-toggle-{{ $magasin->status ? 'on' : 'off' }}"></i>
                            </button>

                            <button wire:click="deleteConfirm({{ $magasin->id }})"
                                    class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    @include('livewire.warehouse.magasin-modal')

</div>

@push('scripts')
<script>
    window.addEventListener('confirm-delete', (e) => {
        confirmAction({
            title: 'Supprimer le magasin ?',
            text: 'Cette action est irréversible',
            confirmText: 'Supprimer',
            onConfirm: () => {
                Livewire.dispatch('confirmDelete', { id: e.detail.id })
            }
        })
    })
</script>
@endpush
