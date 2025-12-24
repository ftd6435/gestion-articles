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
                                        <option value="">Sélectionner</option>
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
                                        <option value="">Sélectionner</option>
                                        @foreach($receptions as $reception)
                                            <option value="{{ $reception->id }}">
                                                Rec #{{ $reception->id }} -
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
                                              wire:model.defer="notes"></textarea>
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
        {{-- <div lass="modal fade show d-block position-fixed top-0 start-0 w-100 h-100" tabindex="-1" style="background:rgba(0,0,0,.6)"> --}}
        <div wire:show="showDetailsModal"
            x-transition.opacity.duration.200ms
            x-transition.scale.duration.200ms
            class="modal-backdrop-custom"
            >
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal" style="z-index: 1060;">
                <div class="modal-content border-0 shadow-lg" id="paiementDetailsContent">
                    <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);">
                        <div>
                            <h5 class="modal-title" id="detailsModalLabel">
                                <i class="fas fa-file-invoice me-2"></i>Détails du paiement
                            </h5>
                            <small class="text-white fw-semibold opacity-75">REF: {{ $selectedPaiement->reference }}</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-white ">
                        <div class="p-4" id="paiementDetailsContent">
                            {{-- Header Info --}}
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Fournisseur</div>
                                            <div class="fw-bold h6">{{ $selectedPaiement->commande?->fournisseur?->name ?? '—' }}</div>
                                            <small class="text-muted">
                                                {{ $selectedPaiement->commande?->fournisseur?->email ?? '' }}
                                                {{ $selectedPaiement->commande?->fournisseur?->phone ? ' • ' . $selectedPaiement->commande?->fournisseur?->phone : '' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Montant payé</div>
                                            <div class="fw-bold h4 text-success">
                                                @php
                                                    $currency = $selectedPaiement->commande?->devise?->symbole ?? $selectedPaiement->commande?->devise?->code ?? 'FC';
                                                @endphp
                                                {{ number_format($selectedPaiement->montant, 0, ',', ' ') }} {{ $currency }}
                                            </div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($selectedPaiement->date_paiement)->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Mode de paiement</div>
                                            <div class="fw-bold h6 mb-1">
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
                                                    {{ $modesPaiement[$selectedPaiement->mode_paiement] ?? $selectedPaiement->mode_paiement }}
                                                </span>
                                            </div>
                                            <div class="text-muted small">
                                                Réf: {{ $selectedPaiement->reference }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Command & Reception Details --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="fas fa-shopping-cart me-2"></i>Informations Commande
                                            </h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="text-muted small">Référence</div>
                                                    <div class="fw-semibold">{{ $selectedPaiement->commande?->reference ?? '—' }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Date commande</div>
                                                    <div class="fw-semibold">
                                                        {{ optional($selectedPaiement->commande?->date_commande)->format('d/m/Y') ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="text-muted small">Devise</div>
                                                <div class="fw-semibold">
                                                    {{ $selectedPaiement->commande?->devise?->code ?? '—' }}
                                                    ({{ $selectedPaiement->commande?->devise?->symbole ?? '' }})
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="fas fa-truck me-2"></i>Informations Réception
                                            </h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="text-muted small">Référence</div>
                                                    <div class="fw-semibold">Réception #{{ $selectedPaiement->reception?->id ?? '—' }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Date réception</div>
                                                    <div class="fw-semibold">
                                                        {{ optional($selectedPaiement->reception?->date_reception)->format('d/m/Y') ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="text-muted small">Total réception</div>
                                                <div class="fw-semibold">
                                                    @if($selectedPaiement->reception)
                                                        {{ number_format($selectedPaiement->reception->getTotalAmount(), 0, ',', ' ') }} {{ $currency }}
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Articles réceptionnés --}}
                            @if($selectedPaiement->reception && $selectedPaiement->reception->ligneReceptions->count() > 0)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">
                                        <i class="fas fa-boxes me-2"></i>Articles réceptionnés
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Article</th>
                                                    <th class="text-center">Quantité</th>
                                                    <th class="text-end">Prix unitaire</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalArticles = 0;
                                                @endphp
                                                @foreach($selectedPaiement->reception->ligneReceptions as $index => $ligne)
                                                    @php
                                                        // Get unit price from commande
                                                        $unitPrice = 0;
                                                        $articleTotal = 0;
                                                        if($selectedPaiement->commande && $selectedPaiement->commande->ligneCommandes) {
                                                            $ligneCommande = $selectedPaiement->commande->ligneCommandes->firstWhere('article_id', $ligne->article_id);
                                                            $unitPrice = $ligneCommande?->unit_price ?? 0;
                                                            $articleTotal = $ligne->quantity * $unitPrice;
                                                            $totalArticles += $articleTotal;
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="fw-medium">{{ $ligne->article?->designation ?? '—' }}</div>
                                                            <small class="text-muted">Code: {{ $ligne->article?->reference ?? '—' }}</small>
                                                        </td>
                                                        <td class="text-center">{{ $ligne->quantity }}</td>
                                                        <td class="text-end">{{ number_format($unitPrice, 2, ',', ' ') }} {{ $currency }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($articleTotal, 2, ',', ' ') }} {{ $currency }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total articles:</td>
                                                    <td class="text-end fw-bold">{{ number_format($totalArticles, 2, ',', ' ') }} {{ $currency }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Payment History --}}
                            @if($selectedPaiement->reception && $selectedPaiement->reception->paiements->count() > 0)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">
                                        <i class="fas fa-history me-2"></i>Historique des paiements
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Référence</th>
                                                    <th>Mode</th>
                                                    <th class="text-end">Montant</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedPaiement->reception->paiements->sortBy('date_paiement') as $payment)
                                                    <tr class="{{ $payment->id == $selectedPaiement->id ? 'table-primary' : '' }}">
                                                        <td>{{ \Carbon\Carbon::parse($payment->date_paiement)->format('d/m/Y H:i') }}</td>
                                                        <td>{{ $payment->reference }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                                {{ $modesPaiement[$payment->mode_paiement] ?? $payment->mode_paiement }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end fw-bold">{{ number_format($payment->montant, 0, ',', ' ') }} {{ $currency }}</td>
                                                        <td>
                                                            @if($payment->id == $selectedPaiement->id)
                                                                <span class="badge bg-info">Paiement actuel</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @if($selectedPaiement->reception)
                                                    @php
                                                        $totalPaid = $selectedPaiement->reception->getTotalPaid();
                                                        $totalAmount = $selectedPaiement->reception->getTotalAmount();
                                                        $remaining = $selectedPaiement->reception->getRemainingAmount();
                                                    @endphp
                                                    <tr class="table-light">
                                                        <td colspan="3" class="text-end fw-bold">Total payé:</td>
                                                        <td class="text-end fw-bold text-success">{{ number_format($totalPaid, 2, ',', ' ') }} {{ $currency }}</td>
                                                        <td></td>
                                                    </tr>
                                                    <tr class="table-light">
                                                        <td colspan="3" class="text-end fw-bold">Total réception:</td>
                                                        <td class="text-end fw-bold">{{ number_format($totalAmount, 2, ',', ' ') }} {{ $currency }}</td>
                                                        <td></td>
                                                    </tr>
                                                    <tr class="table-light">
                                                        <td colspan="3" class="text-end fw-bold">Reste à payer:</td>
                                                        <td class="text-end fw-bold {{ $remaining > 0 ? 'text-warning' : 'text-success' }}">
                                                            {{ number_format($remaining, 2, ',', ' ') }} {{ $currency }}
                                                        </td>
                                                        <td>
                                                            @if($remaining <= 0)
                                                                <span class="badge bg-success">Payé</span>
                                                            @elseif($remaining < $totalAmount)
                                                                <span class="badge bg-warning">Partiel</span>
                                                            @else
                                                                <span class="badge bg-danger">Impayé</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Notes --}}
                            @if($selectedPaiement->notes)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-sticky-note me-2"></i>Notes
                                    </h6>
                                    <div class="card border">
                                        <div class="card-body">
                                            {{ $selectedPaiement->notes }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Audit Trail --}}
                            <div class="row mt-4 pt-4 border-top">
                                <div class="col-md-6">
                                    <div>
                                        <span class="text-muted small">Créé par: </span>
                                        <span class="fw-semibold small">{{ $selectedPaiement->createdBy?->name ?? '—' }}</span>
                                    </div>
                                    <div class="text-muted small">{{ $selectedPaiement->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div>
                                        <span class="text-muted small">Dernière modification: </span>
                                        <span class="fw-semibold small">{{ $selectedPaiement->updatedBy?->name ?? '—' }}</span>
                                    </div>
                                    <div class="text-muted small">{{ $selectedPaiement->updated_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-4" style="background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" wire:click="closeDetailsModal">
                                    <i class="fas fa-times me-2"></i>Fermer
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="printPaiement()">
                                    <i class="fas fa-print me-2"></i>Imprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>
