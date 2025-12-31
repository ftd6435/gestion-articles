<div class="container-fluid py-4">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-primary mb-1">
                <i class="fas fa-credit-card me-2"></i> Gestion des paiements fournisseurs
            </h1>
            <p class="text-muted mb-0">
                Suivi des paiements par commande et réception
            </p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" wire:click="create">
                <i class="fas fa-plus-circle me-2"></i> Nouveau paiement
            </button>
            <button class="btn btn-outline-secondary" wire:click="resetFilters">
                <i class="fas fa-rotate-left me-2"></i> Réinitialiser
            </button>
        </div>
    </div>

     {{-- ================= FILTERS ================= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">

                {{-- Search --}}
                <div class="col-md-4">
                    <input type="text"
                           class="form-control"
                           wire:model.live.debounce.200ms="search"
                           placeholder="Référence, fournisseur…">
                </div>

                {{-- Period --}}
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="period">
                        <option value="">Période</option>
                        <option value="today">Aujourd’hui</option>
                        <option value="weekly">Cette semaine</option>
                        <option value="monthly">Ce mois</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>

                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterMode">
                        <option value="">Mode</option>
                        @foreach($modesPaiement as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="bg-primary text-white">Référence</th>
                        <th class="bg-primary text-white">Commande</th>
                        <th class="bg-primary text-white">Réception</th>
                        <th class="bg-primary text-white">Fournisseur</th>
                        <th class="bg-primary text-white">Date</th>
                        <th class="bg-primary text-white">Montant</th>
                        <th class="bg-primary text-white">Mode</th>
                        <th class="bg-primary text-white">Statut</th>
                        <th class="text-center bg-primary text-white pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paiements as $paiement)
                        @php
                            $reception = $paiement->reception;
                            $currency = $paiement->commande?->devise?->symbole
                                ?? $paiement->commande?->devise?->code
                                ?? 'FG';
                        @endphp
                        <tr>
                            <td class="fw-semibold text-primary">
                                {{ $paiement->reference }}
                            </td>
                            <td>{{ $paiement->commande?->reference }}</td>
                            <td>{{ $paiement->reception?->reference }}</td>
                            <td>{{ $paiement->commande?->fournisseur?->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                            <td class="fw-bold">
                                {{ number_format($paiement->montant, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td>
                                @php
                                    $modeColors = [
                                        'ESPECES' => 'success',
                                        'CHEQUE' => 'primary',
                                        'VIREMENT' => 'info',
                                        'MOBILE' => 'warning',
                                        'CARTE' => 'secondary',
                                    ];
                                    $color = $modeColors[$paiement->mode_paiement] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                    {{ $modesPaiement[$paiement->mode_paiement] ?? $paiement->mode_paiement }}
                                </span>
                            </td>
                            <td>
                                @if($reception)
                                    @php
                                        $status = $reception->getPaymentStatus();
                                    @endphp

                                    <span class="badge
                                        {{ $status === 'PAYE' ? 'bg-success' :
                                           ($status === 'PARTIEL' ? 'bg-warning' : 'bg-secondary') }}
                                        bg-opacity-10 text-dark">
                                        {{ $status }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-outline-info"
                                            wire:click="showDetails({{ $paiement->id }})"
                                            data-bs-toggle="tooltip"
                                            title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary"
                                            wire:click="edit({{ $paiement->id }})"
                                            data-bs-toggle="tooltip"
                                            title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            wire:click="deleteConfirm({{ $paiement->id }})"
                                            data-bs-toggle="tooltip"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fa-3x mb-3"></i>
                                <div>Aucun paiement enregistré</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer bg-light">
            {{ $paiements->links() }}
        </div>
    </div>

    {{-- ================= MODAL ================= --}}
    @if($showModal)
        <div wire:show="showModal"
            x-transition.opacity.duration.200ms
            x-transition.scale.duration.200ms
            class="modal-backdrop-custom"
            >
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">

                    {{-- Header --}}
                    <div class="modal-header text-white p-4"
                         style="background:linear-gradient(135deg,#667eea,#764ba2)">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-credit-card me-2"></i>Nouveau paiement
                        </h5>
                        <button class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>

                    <form wire:submit.prevent="store">

                        {{-- Body --}}
                        <div class="modal-body bg-white p-4">

                            {{-- Summary --}}
                            @if($selectedReception)
                                @php
                                    $currency = $selectedReception->commande?->devise?->symbole
                                        ?? $selectedReception->commande?->devise?->code
                                        ?? 'FC';
                                @endphp
                                <div class="row text-center mb-4">
                                    <div class="col-4">
                                        <div class="text-muted small">Total</div>
                                        <div class="fw-bold text-info">
                                            {{ number_format($selectedReception->getTotalAmount(),0,',',' ') }}
                                            {{ $currency }}
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Payé</div>
                                        <div class="fw-bold text-success">
                                            {{ number_format($selectedReception->getTotalPaid(),0,',',' ') }}
                                            {{ $currency }}
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Reste</div>
                                        <div class="fw-bold text-warning">
                                            {{ number_format($selectedReception->getRemainingAmount(),0,',',' ') }}
                                            {{ $currency }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row g-3">

                                {{-- Commande --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Commande *</label>
                                    <select class="form-select @error('commande_id') is-invalid @enderror"
                                            wire:model.live="commande_id">
                                        <option value="">Sélectionner Commande</option>
                                        @foreach($commandes as $commande)
                                            <option value="{{ $commande['id'] }}">
                                                {{ $commande['reference'] }} -
                                                {{ $commande['fournisseur']['name'] ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('commande_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Réception --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Réception *</label>
                                    <select class="form-select @error('reception_id') is-invalid @enderror"
                                            wire:model="reception_id"
                                            {{ !$commande_id ? 'disabled' : '' }}>
                                        <option value="">Sélectionner Réception</option>
                                        @foreach($receptions as $reception)
                                            <option value="{{ $reception->id }}">
                                                {{ $reception?->reference }} -
                                                Reste :
                                                {{ number_format($reception->getRemainingAmount(),0,',',' ') }} {{ $reception->commande->devise->symbole ?? $reception->commande->devise->symbole }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('reception_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Date *</label>
                                    <input type="date"
                                           class="form-control @error('date_paiement') is-invalid @enderror"
                                           wire:model="date_paiement">
                                    @error('date_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Montant --}}
                                <div class="col-md-6" x-data="priceInput(@this, 'montant')">
                                    <label class="form-label fw-semibold">Montant *</label>
                                    <div class="input-group">
                                        <input type="text"
                                            x-model="display"
                                            @input="format"
                                            inputmode="decimal"
                                           class="form-control @error('montant') is-invalid @enderror"
                                           wire:model="montant">

                                        <span class="input-group-text">{{ $symbole }}</span>
                                    </div>
                                    @error('montant')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Mode de paiement --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold mb-2">Mode de paiement *</label>

                                    <div class="row g-2">
                                        @foreach($modesPaiement as $value => $label)
                                            @php
                                                $icons = [
                                                    'ESPECES'  => 'fa-money-bill-wave',
                                                    'CHEQUE'   => 'fa-file-invoice',
                                                    'VIREMENT' => 'fa-university',
                                                    'MOBILE'   => 'fa-mobile-alt',
                                                    'CARTE'    => 'fa-credit-card',
                                                ];
                                            @endphp

                                            <div class="col-6 col-md-4">
                                                <label
                                                    class="w-100 p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer
                                                        {{ $mode_paiement === $value ? 'border-primary bg-primary bg-opacity-10' : '' }}">

                                                    <input
                                                        type="radio"
                                                        class="form-check-input mt-0"
                                                        wire:model.live="mode_paiement"
                                                        value="{{ $value }}"
                                                    >

                                                    <i class="fas {{ $icons[$value] ?? 'fa-credit-card' }} text-primary"></i>

                                                    <span class="fw-medium">
                                                        {{ $label }}
                                                    </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    @error('mode_paiement')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Notes --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <textarea class="form-control"
                                              rows="2"
                                              wire:model.defer="notes" placeholder="Commentaire du paiement..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer bg-light px-4 py-3">
                            <button type="button"
                                    class="btn btn-secondary me-2"
                                    wire:click="closeModal">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== MODAL: PAYMENT DETAILS ========== --}}
    @if($showDetailsModal && $selectedPaiement)
        @include('livewire.stock.paiement-details-modal')
    @endif
</div>
