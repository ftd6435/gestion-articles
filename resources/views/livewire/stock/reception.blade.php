<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Approvisionnements</h1>
            <p class="text-muted mb-0">
                Suivi des réceptions fournisseurs
            </p>
        </div>

        <button wire:click="create" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>
            Nouvelle réception
        </button>
    </div>

    {{-- ================= STATS ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-start border-3 border-primary shadow-md">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2 fw-semibold">
                                <i class="fas fa-calendar-week me-2"></i>Aujourd'hui
                            </h6>
                            <h1 class="fw-bold text-primary mb-0">{{ $this->stats['today'] }}</h1>
                            <span class="text-muted small">Réception Journalière</span>
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

        <div class="col-md-4">
            <div class="card border-start border-3 border-success shadow-md">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2 fw-semibold">
                                <i class="fas fa-calendar-alt me-2"></i>Cette sémaine
                            </h6>
                            <h1 class="fw-bold text-success mb-0">{{ $this->stats['weekly'] }}</h1>
                            <span class="text-muted small">Réception Hebdomadaire</span>
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

        <div class="col-md-4">
            <div class="card border-start border-3 border-info shadow-md">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2 fw-semibold">
                                <i class="fas fa-calendar-alt me-2"></i>Ce mois
                            </h6>
                            <h1 class="fw-bold text-info mb-0">{{ $this->stats['monthly'] }}</h1>
                            <span class="text-muted small">Réception Mensuelle</span>
                        </div>
                        <div class="align-self-center">
                            <span class="badge bg-info bg-opacity-10 text-info p-2">
                                <i class="fas fa-chart-bar fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MAIN GRID ================= --}}
    <div class="row g-4">

        {{-- ========== FILTERS ========== --}}
        <div class="col-12 col-md-3">
            <div class="card shadow-sm border-0 sticky-top" style="top: 90px;">
                <div class="card-header bg-primary text-white fw-semibold">
                    <i class="fa fa-filter me-2"></i> Filtres
                </div>

                <div class="card-body">

                    {{-- Search --}}
                    <div class="mb-3">
                        <label class="form-label">Recherche</label>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Référence ou fournisseur">
                    </div>

                    {{-- Commande --}}
                    {{-- <div class="mb-3">
                        <label class="form-label">Commande</label>
                        <select wire:model.live="filterCommande" class="form-select">
                            <option value="">Toutes</option>
                            @foreach($commandes as $commande)
                                <option value="{{ $commande->id }}">
                                    {{ $commande->reference }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

                    {{-- Date range --}}
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

                    {{-- Period --}}
                    <div class="mb-3">
                        <label class="form-label">Période rapide</label>
                        <select wire:model.live="period" class="form-select">
                            <option value="">Aucune</option>
                            <option value="weekly">Cette semaine</option>
                            <option value="monthly">Ce mois</option>
                        </select>
                    </div>

                    <button wire:click="resetFilters"
                            class="btn btn-outline-secondary w-100">
                        <i class="fa fa-rotate-left me-1"></i>
                        Réinitialiser
                    </button>

                </div>
            </div>
        </div>

        {{-- ========== LIST ========== --}}
        <div class="col-12 col-md-9">
            <div class="card shadow-sm border-0">
                @include('components.shared.alerts')

                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Liste des réceptions</span>
                    <span class="badge bg-white text-black">{{ $receptions->total() }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Ref</th>
                                <th>Commande</th>
                                <th>Fournisseur</th>
                                <th>Qté</th>
                                <th>Date</th>
                                <th>Créé</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($receptions as $reception)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">
                                        {{ $reception->reference }}
                                    </td>
                                    <td class="text-muted">
                                        {{ $reception->commande->reference }}
                                    </td>

                                    <td class="text-muted">
                                        {{ $reception->commande->fournisseur->name }}
                                    </td>

                                    <td class="text-muted fw-semibold">
                                        {{ $reception->ligneReceptions()->sum('quantity') }}
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}
                                    </td>

                                    <td class="text-muted">
                                        {{ $reception->createdBy->name ?? '—' }}
                                    </td>

                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button wire:click="showDetails({{ $reception->id }})"
                                                    class="btn btn-outline-info"
                                                    title="Détails">
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            <button wire:click="deleteConfirm({{ $reception->id }})"
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
                                        <i class="fa fa-truck-loading fa-2x mb-2 opacity-50"></i>
                                        <div>Aucune réception trouvée</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-white">
                    {{ $receptions->links() }}
                </div>

            </div>
        </div>

    </div>

    {{-- ================= MODALS ================= --}}
    @if($showModal)
        @include('livewire.stock.reception-modal')
    @endif

    @if($showDetailsModal)
        @include('livewire.stock.reception-details-modal')
    @endif

</div>
