<div>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Commandes fournisseurs</h1>
            <p class="text-muted mb-0">Suivi et gestion des commandes fournisseurs</p>
        </div>

        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>
            Nouvelle commande
        </button>
    </div>

    {{-- Statistique --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-start border-3 border-primary shadow-md">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2 fw-semibold">
                                <i class="fas fa-calendar-week me-2"></i>Cette semaine
                            </h6>
                            <h1 class="fw-bold text-primary mb-0">{{ $this->stats['weekly'] }}</h1>
                            <span class="text-muted small">Commandes totales</span>
                        </div>
                        <div class="align-self-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary p-2">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-start border-3 border-success shadow-md">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2 fw-semibold">
                                <i class="fas fa-calendar-alt me-2"></i>Ce mois
                            </h6>
                            <h1 class="fw-bold text-success mb-0">{{ $this->stats['monthly'] }}</h1>
                            <span class="text-muted small">Commandes totales</span>
                        </div>
                        <div class="align-self-center">
                            <span class="badge bg-success bg-opacity-10 text-success p-2">
                                <i class="fas fa-chart-bar fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="row g-4">

        <!-- ================= FILTERS ================= -->
        <div class="col-12 col-md-3">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold bg-info text-white">
                    <i class="fa fa-filter me-2"></i> Filtres
                </div>

                <div class="card-body">

                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label">Recherche</label>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Référence ou fournisseur">
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Tous</option>
                            <option value="EN_COURS">En cours</option>
                            <option value="PARTIELLE">Partielle</option>
                            <option value="TERMINEE">Terminée</option>
                            <option value="ANNULEE">Annulée</option>
                        </select>
                    </div>

                    <!-- Fournisseur -->
                    <div class="mb-3">
                        <label class="form-label">Fournisseur</label>
                        <select wire:model.live="filterFournisseur" class="form-select">
                            <option value="">Tous</option>
                            @foreach($fournisseurs as $fournisseur)
                                <option value="{{ $fournisseur->id }}">
                                    {{ $fournisseur->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date range -->
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label">Du</label>
                            <input type="date" wire:model.live="dateFrom" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Au</label>
                            <input type="date" wire:model.live="dateTo" class="form-control">
                        </div>
                    </div>

                    <!-- Period -->
                    <div class="mb-3">
                        <label class="form-label">Période rapide</label>
                        <select wire:model.live="period" class="form-select">
                            <option value="">Aucune</option>
                            <option value="weekly">Cette semaine</option>
                            <option value="monthly">Ce mois</option>
                        </select>
                    </div>

                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="fa fa-rotate-left me-1"></i>
                        Réinitialiser
                    </button>

                </div>
            </div>
        </div>

        <!-- ================= LIST ================= -->
        <div class="col-12 col-md-9">
            <div class="card shadow-sm border-0">

                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-white">Liste des commandes</span>
                    <span class="badge bg-white text-black">{{ count($commandes) }} commande(s)</span>
                </div>

                @include('components.shared.alerts')

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Référence</th>
                                <th>Fournisseur</th>
                                <th>Qté</th>
                                <th>Reçue</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($commandes as $commande)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td class="fw-semibold">
                                        {{ $commande->reference }}
                                    </td>

                                    <td class="text-muted">
                                        {{ $commande->fournisseur->name ?? '—' }}
                                    </td>

                                    <td class="text-muted fw-semibold">
                                        {{ $commande->ligneCommandes()->sum('quantity') ?? 0 }}
                                    </td>

                                    <td class="text-success fw-semibold">
                                        {{
                                            $commande->receptions
                                                ->flatMap(fn ($r) => $r->ligneReceptions)
                                                ->sum('quantity')
                                        }}
                                    </td>

                                    <td>
                                        {{ $commande->date_commande
                                            ? \Carbon\Carbon::parse($commande->date_commande)->format('d/m/Y')
                                            : '—'
                                        }}
                                    </td>

                                    <td>
                                        @php
                                            $statusClass = match($commande->status) {
                                                'EN_COURS'   => 'bg-info',
                                                'PARTIELLE'  => 'bg-warning text-dark',
                                                'TERMINEE'   => 'bg-success',
                                                'ANNULEE'    => 'bg-danger',
                                                default      => 'bg-secondary',
                                            };
                                        @endphp

                                        <span class="badge {{ $statusClass }}">
                                            {{ str_replace('_', ' ', $commande->status) }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            @if (! $commande->receptions()->exists())
                                                <button
                                                    data-bs-toggle="tooltip"
                                                    title="Annuler commande"
                                                    wire:click="cancelCommande({{ $commande->id }})"
                                                        class="btn btn-outline-danger">
                                                    <i class="fa fa-close"></i>
                                                </button>
                                            @endif

                                            <button
                                                data-bs-toggle="tooltip"
                                                title="Modifier"
                                                wire:click="edit({{ $commande->id }})"
                                                    class="btn btn-outline-warning">
                                                <i class="fa fa-pen"></i>
                                            </button>

                                            <button
                                                data-bs-toggle="tooltip"
                                                title="Voir facture"
                                                wire:click="showDetails({{ $commande->id }})"
                                                    class="btn btn-outline-primary">
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            @if (! $commande->receptions()->exists())
                                                <button
                                                    data-bs-toggle="tooltip"
                                                    title="Supprimer"
                                                    wire:click="deleteConfirm({{ $commande->id }})"
                                                        class="btn btn-outline-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Aucune commande trouvée
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $commandes->links() }}
                </div>
            </div>
        </div>

    </div>

    @if($showModal)
        @include('livewire.stock.commande-modal')
    @endif

    @if($showModalDetails)
        @include('livewire.stock.commande-details')
    @endif
</div>
