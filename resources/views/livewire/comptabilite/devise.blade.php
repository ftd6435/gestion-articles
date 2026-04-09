<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Devise</h1>
            <p class="text-muted mb-0">Gérez vos devises</p>
        </div>
        @access('configuration.devises', 'create')
            <button wire:click="create" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i> Nouvelle devise
            </button>
        @endaccess
    </div>

    <!-- devises Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Liste des devises</h5>
            <span class="badge bg-primary">{{ count($devises) }} devise(s)</span>
        </div>

        <div class="card-body p-0">
            <!-- Desktop/Tablet Table View -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Symbole</th>
                            <th width="100">Status</th>
                            <th width="120">Par défaut</th>
                            <th width="180" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devises as $devise)
                            <tr class="{{ $devise->is_default ? 'table-success' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $devise->code }}</td>
                                <td class="text-muted">
                                    {{ $devise->libelle ? Str::limit($devise->libelle, 50) : '—' }}
                                </td>
                                <td class="text-muted">
                                    {{ $devise->symbole ? Str::limit($devise->symbole, 50) : 'N/D' }}
                                </td>
                                <td>
                                    @access('configuration.devises', 'toggle_status')
                                        <button wire:click="toggleStatusConfirm({{ $devise->id }})"
                                                class="btn btn-sm {{ $devise->status ? 'btn-success' : 'btn-secondary' }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ $devise->status ? 'Désactiver' : 'Activer' }}">
                                            {{ $devise->status ? 'Actif' : 'Inactif' }}
                                        </button>
                                    @else
                                        <span class="badge {{ $devise->status ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $devise->status ? 'Actif' : 'Inactif' }}
                                        </span>
                                    @endaccess
                                </td>
                                <td>
                                    @if($devise->is_default)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Défaut
                                        </span>
                                    @else
                                        @access('configuration.devises', 'update')
                                            <button wire:click="toggleDefaultConfirm({{ $devise->id }})"
                                                    class="btn btn-sm {{ $devise->status ? 'btn-outline-secondary' : 'btn-secondary' }}"
                                                    title="{{ $devise->status ? 'Définir comme devise par défaut' : 'Devise inactive' }}"
                                                    {{ !$devise->status ? 'disabled' : '' }}>
                                                @if($devise->status)
                                                    <i class="fas fa-star me-1"></i> Définir
                                                @else
                                                    <i class="fas fa-ban me-1"></i> Inactive
                                                @endif
                                            </button>
                                        @else
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-lock me-1"></i> —
                                            </span>
                                        @endaccess
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @access('configuration.devises', 'update')
                                            <button wire:click="edit({{ $devise->id }})"
                                                    class="btn btn-outline-primary"
                                                    data-bs-toggle="tooltip"
                                                    title="Modifier">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        @endaccess

                                        @if(!$devise->is_default)
                                        <button wire:click="deleteConfirm({{ $devise->id }})"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="tooltip"
                                                title="Supprimer">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Aucune devise trouvée</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                @forelse($devises as $devise)
                    <div class="border-bottom p-3 {{ $devise->is_default ? 'bg-light-success' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">{{ $devise->code }} | <span class="text-muted">{{ $devise->symbole ? $devise->symbole : 'Aucun symbole' }} </span></h6>
                                <p class="text-muted small mb-2">
                                    {{ $devise->libelle ? Str::limit($devise->libelle, 60) : 'Aucun libelle' }}
                                </p>

                                <div class="d-flex gap-2 mb-2">
                                    @access('configuration.devises', 'toggle_status')
                                        <button wire:click="toggleStatusConfirm({{ $devise->id }})"
                                                class="btn btn-sm {{ $devise->status ? 'btn-success' : 'btn-secondary' }}">
                                            {{ $devise->status ? 'Actif' : 'Inactif' }}
                                        </button>
                                    @else
                                        <span class="badge {{ $devise->status ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $devise->status ? 'Actif' : 'Inactif' }}
                                        </span>
                                    @endaccess

                                    @if($devise->is_default)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Défaut
                                        </span>
                                    @else
                                        @access('configuration.devises', 'update')
                                            <button wire:click="toggleDefaultConfirm({{ $devise->id }})"
                                                    class="btn btn-sm {{ $devise->status ? 'btn-outline-secondary' : 'btn-secondary' }}"
                                                    {{ !$devise->status ? 'disabled' : '' }}>
                                                @if($devise->status)
                                                    <i class="fas fa-star me-1"></i> Définir
                                                @else
                                                    <i class="fas fa-ban me-1"></i> Inactive
                                                @endif
                                            </button>
                                        @else
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-lock me-1"></i> —
                                            </span>
                                        @endaccess
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            @access('configuration.devises', 'update')
                                <button wire:click="edit({{ $devise->id }})"
                                        class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fa fa-pen me-1"></i> Modifier
                                </button>
                            @endaccess

                            @if(!$devise->is_default)
                            <button wire:click="deleteConfirm({{ $devise->id }})"
                                    class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Aucune devise trouvée</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
        @include('livewire.comptabilite.devise-modal')
    @endif

    <style>
        .bg-light-success {
            background-color: rgba(25, 135, 84, 0.05) !important;
        }

        .table-success {
            --bs-table-bg: rgba(25, 135, 84, 0.05);
            --bs-table-striped-bg: rgba(25, 135, 84, 0.1);
            --bs-table-striped-color: #000;
            --bs-table-active-bg: rgba(25, 135, 84, 0.1);
            --bs-table-active-color: #000;
            --bs-table-hover-bg: rgba(25, 135, 84, 0.075);
            --bs-table-hover-color: #000;
            color: #000;
            border-color: rgba(25, 135, 84, 0.1);
        }
    </style>
</div>
