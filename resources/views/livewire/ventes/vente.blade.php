<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i>Ventes clients
        </h4>

        <button class="btn btn-primary" wire:click="createVente">
            <i class="fas fa-plus me-2"></i>Nouvelle vente
        </button>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Ventes</h6>
                            <h4 class="mb-0 fw-bold">{{ $totalVentes }}</h4>
                        </div>
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Payé</h6>
                            <h4 class="mb-0 fw-bold text-success">
                                {{ number_format($totalPaid, 0, ',', ' ') }}
                            </h4>
                        </div>
                        <div class="bg-success text-white rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Dû</h6>
                            <h4 class="mb-0 fw-bold text-danger">
                                {{ number_format($totalDue, 0, ',', ' ') }}
                            </h4>
                        </div>
                        <div class="bg-danger text-white rounded-circle p-3">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">En cours</h6>
                            <h4 class="mb-0 fw-bold text-info">{{ $ventesInProgress }}</h4>
                        </div>
                        <div class="bg-info text-white rounded-circle p-3">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <input type="text"
                           class="form-control"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Référence ou client...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">Tous</option>
                        <option value="PAYEE">Payée</option>
                        <option value="PARTIELLE">Partielle</option>
                        <option value="IMPAYEE">Impayée</option>
                        <option value="ANNULEE">Annulée</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date de début</label>
                    <input type="date"
                           class="form-control"
                           wire:model.live.debounce.200ms="date_from">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date de fin</label>
                    <input type="date"
                           class="form-control"
                           wire:model.live.debounce.200ms="date_to">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100"
                            wire:click="resetFilters"
                            title="Réinitialiser les filtres">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="bg-primary text-white">Référence</th>
                        <th class="bg-primary text-white">Client</th>
                        <th class="bg-primary text-white">Date</th>
                        <th class="text-end bg-primary text-white">Montant</th>
                        <th class="text-end bg-primary text-white">Payé</th>
                        <th class="text-end bg-primary text-white">Reste</th>
                        <th class="bg-primary text-white">Statut</th>
                        <th class="text-end bg-primary text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->ventes as $vente)
                        @php
                            $total = $vente->totalAfterRemise();
                            $paid = $vente->paiements()->sum('montant');
                            $remaining = max(0, $total - $paid);
                            $currency = $vente->devise?->symbole ?? $vente->devise?->code ?? 'FG';
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $vente->reference }}</td>
                            <td>{{ $vente->client?->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($vente->date_facture)->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold">
                                {{ number_format($total, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end text-success">
                                {{ number_format($paid, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end fw-bold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($remaining, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td>
                                @php
                                    $config = match($vente->status) {
                                        'PAYEE' => [
                                            'class' => 'bg-success bg-opacity-10 text-success border-success',
                                            'icon' => 'fa-check-circle',
                                            'label' => 'Payée'
                                        ],
                                        'PARTIELLE' => [
                                            'class' => 'bg-warning bg-opacity-10 text-warning border-warning',
                                            'icon' => 'fa-exclamation-circle',
                                            'label' => 'Partielle'
                                        ],
                                        'IMPAYEE' => [
                                            'class' => 'bg-danger bg-opacity-10 text-danger border-danger',
                                            'icon' => 'fa-times-circle',
                                            'label' => 'Impayée'
                                        ],
                                        'ANNULEE' => [
                                            'class' => 'bg-secondary bg-opacity-10 text-secondary border-secondary',
                                            'icon' => 'fa-ban',
                                            'label' => 'Annulée'
                                        ],
                                        default => [
                                            'class' => 'bg-info bg-opacity-10 text-info border-info',
                                            'icon' => 'fa-info-circle',
                                            'label' => ucfirst($vente->status)
                                        ],
                                    };
                                @endphp

                                <span class="badge {{ $config['class'] }} border border-opacity-25">
                                    <i class="fas {{ $config['icon'] }} me-1"></i> {{ $config['label'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary"
                                            wire:click="showDetails({{ $vente->id }})"
                                            title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if(in_array($vente->status, ['IMPAYEE', 'PARTIELLE']))
                                        <button class="btn btn-outline-success"
                                                wire:click="canPaiementModal({{ $vente->id }})"
                                                title="Enregistrer un paiement">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                    @endif

                                    @if($vente->status === 'IMPAYEE')
                                        <button class="btn btn-outline-warning"
                                                wire:click="canCancelVente({{ $vente->id }})"
                                                title="Annuler la vente">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif

                                    @if($vente->status === 'ANNULEE')
                                        <button class="btn btn-outline-danger"
                                                wire:click="canDeleteModal({{ $vente->id }})"
                                                title="Supprimer la vente">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">Aucune vente trouvée</p>
                                @if($search || $status || $date_from || $date_to)
                                    <p class="small mt-2">
                                        <button class="btn btn-sm btn-link" wire:click="resetFilters">
                                            Réinitialiser les filtres
                                        </button>
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($this->ventes->hasPages())
            <div class="card-footer border-0 bg-light">
                {{ $this->ventes->links() }}
            </div>
        @endif
    </div>

    {{-- Details Modal --}}
    @if($showDetailsModal && $selectedVente)
        @include('livewire.ventes.vente-details-modal')
    @endif

    {{-- Paiement Modal --}}
    @if($showPaiementModal && $selectedVente)
        @include('livewire.ventes.vente-paiement-modal')
    @endif

    {{-- Cancel Modal --}}
    @if($showCancelModal && $selectedVente)
        @include('livewire.ventes.vente-cancel-modal')
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal && $selectedVente)
        @include('livewire.ventes.vente-delete-modal')
    @endif

</div>
