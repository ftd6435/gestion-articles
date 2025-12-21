<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Catégories des articles</h1>
            <p class="text-muted mb-0">Gérez les catégories de vos articles</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i> Nouvelle catégorie
        </button>
    </div>

    <!-- Categories Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Liste des catégories</h5>
            <span class="badge bg-primary">{{ count($categories) }} catégorie(s)</span>
        </div>

        <div class="card-body p-0">
            @include('components.shared.alerts')
            
            <!-- Desktop/Tablet Table View -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th width="100">Status</th>
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td class="text-muted">
                                    {{ $category->description ? Str::limit($category->description, 50) : '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $category->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $category->status ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button wire:click="edit({{ $category->id }})"
                                                class="btn btn-outline-primary"
                                                data-bs-toggle="tooltip"
                                                title="Modifier">
                                            <i class="fa fa-pen"></i>
                                        </button>

                                        <button wire:click="toggleStatus({{ $category->id }})"
                                                class="btn btn-outline-{{ $category->status ? 'success' : 'secondary' }}"
                                                data-bs-toggle="tooltip"
                                                title="Changer le status">
                                            <i class="fa fa-toggle-{{ $category->status ? 'on' : 'off' }}"></i>
                                        </button>

                                        <button wire:click="deleteConfirm({{ $category->id }})"
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
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Aucune catégorie trouvée</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                @forelse($categories as $category)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">{{ $category->name }}</h6>
                                <p class="text-muted small mb-2">
                                    {{ $category->description ? Str::limit($category->description, 60) : 'Aucune description' }}
                                </p>
                                <span class="badge {{ $category->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $category->status ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button wire:click="edit({{ $category->id }})"
                                    class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fa fa-pen me-1"></i> Modifier
                            </button>

                            <button wire:click="toggleStatus({{ $category->id }})"
                                    class="btn btn-sm btn-outline-{{ $category->status ? 'success' : 'secondary' }}">
                                <i class="fa fa-toggle-{{ $category->status ? 'on' : 'off' }}"></i>
                            </button>

                            <button wire:click="deleteConfirm({{ $category->id }})"
                                    class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Aucune catégorie trouvée</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal -->
    @include('livewire.articles.category-modal')
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('confirm-delete', ({ id }) => {
            if (confirm('Supprimer cette catégorie ?')) {
                Livewire.dispatch('confirmDelete', { id });
            }
        });
    });
</script>
@endpush
