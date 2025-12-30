<div>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Fournisseurs</h1>
            <p class="text-muted mb-0">Gérez vos fournisseurs et leurs informations</p>
        </div>

        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>
            Nouveau fournisseur
        </button>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0">

        <!-- Card Header with Tabs -->
        <div class="card-header bg-primary text-white border-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <!-- Tabs -->
                <ul class="nav nav-pills supplier-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link active"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-table"
                            type="button"
                            role="tab">
                            <i class="fa fa-table me-2"></i>
                            Tableau
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-cards"
                            type="button"
                            role="tab">
                            <i class="fa fa-id-card me-2"></i>
                            Cartes
                        </button>
                    </li>
                </ul>

                <!-- Counter -->
                <span class="badge bg-white text-primary px-3 py-2 fw-semibold">
                    {{ count($fournisseurs) }} fournisseur(s)
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @include('components.shared.alerts')

            <div class="tab-content">

                <!-- ================= TABLE TAB ================= -->
                <div class="tab-pane fade show active" id="tab-table" role="tabpanel">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="60"></th>
                                    <th>Nom</th>
                                    <th>Téléphone</th>
                                    <th>Adresse</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($fournisseurs as $fournisseur)
                                    <tr>

                                        <!-- Avatar -->
                                        <td>
                                            <div class="supplier-avatar">
                                                {{ strtoupper(substr($fournisseur->name, 0, 2)) }}
                                            </div>
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $fournisseur->name }}
                                        </td>

                                        <td class="text-muted">
                                            {{ $fournisseur->telephone }}
                                        </td>

                                        <td class="text-muted">
                                            {{ $fournisseur->adresse ?? '—' }}
                                        </td>

                                        <td>
                                            <span class="badge {{ $fournisseur->status ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $fournisseur->status ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">

                                                <button wire:click="showDetails({{ $fournisseur->id }})"
                                                        class="btn btn-outline-info"
                                                        title="Voir les détails">
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                <button wire:click="edit({{ $fournisseur->id }})"
                                                        class="btn btn-outline-primary"
                                                        title="Modifier">
                                                    <i class="fa fa-pen"></i>
                                                </button>

                                                <button wire:click="toggleStatus({{ $fournisseur->id }})"
                                                        class="btn btn-outline-{{ $fournisseur->status ? 'success' : 'secondary' }}"
                                                        title="Activer / Désactiver">
                                                    <i class="fa fa-toggle-{{ $fournisseur->status ? 'on' : 'off' }}"></i>
                                                </button>

                                                <button wire:click="deleteConfirm({{ $fournisseur->id }})"
                                                        class="btn btn-outline-danger"
                                                        title="Supprimer">
                                                    <i class="fa fa-trash"></i>
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fa fa-users fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">Aucun fournisseur trouvé</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- ================= CARD TAB ================= -->
                <div class="tab-pane fade" id="tab-cards" role="tabpanel">

                    <div class="row g-3 p-3">
                        @forelse($fournisseurs as $fournisseur)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <div class="d-flex align-items-center mb-3">
                                            <div class="supplier-avatar me-3">
                                                {{ strtoupper(substr($fournisseur->name, 0, 2)) }}
                                            </div>

                                            <div>
                                                <h6 class="fw-semibold mb-0">
                                                    {{ $fournisseur->name }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $fournisseur->telephone }}
                                                </small>
                                            </div>
                                        </div>

                                        <p class="text-muted small mb-3">
                                            {{ $fournisseur->adresse ?? 'Aucune adresse renseignée' }}
                                        </p>

                                        <span class="badge {{ $fournisseur->status ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $fournisseur->status ? 'Actif' : 'Inactif' }}
                                        </span>

                                    </div>

                                    <div class="card-footer bg-white d-flex gap-2">
                                        <button wire:click="showDetails({{ $fournisseur->id }})"
                                                class="btn btn-sm btn-outline-info flex-fill">
                                            <i class="fa fa-eye me-1"></i>
                                            Détails
                                        </button>

                                        <button wire:click="edit({{ $fournisseur->id }})"
                                                class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-pen"></i>
                                        </button>

                                        <button wire:click="toggleStatus({{ $fournisseur->id }})"
                                                class="btn btn-outline-{{ $fournisseur->status ? 'success' : 'secondary' }}"
                                                title="Activer / Désactiver">
                                            <i class="fa fa-toggle-{{ $fournisseur->status ? 'on' : 'off' }}"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-danger" wire:click="deleteConfirm({{ $fournisseur->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fa fa-users fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Aucun fournisseur trouvé</p>
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>
        </div>
    </div>

    @if ($showModal)
        @include('livewire.fournisseur-modal')
    @endif

    @if ($showDetailsModal && $selectedFournisseur)
        @include('livewire.fournisseur-details-modal')
    @endif
</div>
