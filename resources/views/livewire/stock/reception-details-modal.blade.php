{{-- ========== MODAL: RECEPTION DETAILS ========== --}}
@if($showDetailsModal && $selectedReception)

<div wire:show="showDetailsModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white p-4"
                style="background: linear-gradient(135deg,#4e54c8,#8f94fb)">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-truck-loading me-2"></i>Détails de la réception
                    </h5>
                    <small class="opacity-75">
                        REF: {{ $selectedReception->reference }}
                    </small>
                </div>
                <button class="btn-close btn-close-white"
                    wire:click="closeDetailsModal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                <div class="p-4" id="receptionDetailsContent">

                    @php
                    $commande = $selectedReception->commande;
                    $currency = $commande?->devise?->symbole
                    ?? $commande?->devise?->code
                    ?? 'FG';
                    $discountPercentage = $commande?->remise ?? 0;
                    $totalNoDiscount = $selectedReception->getTotalAmountNoDiscount();
                    $discountAmount = $selectedReception->getDiscountAmount();
                    $totalWithDiscount = $selectedReception->getTotalAmount();
                    $totalPaid = $selectedReception->getTotalPaid();
                    $remainingAmount = $selectedReception->getRemainingAmount();
                    $paymentStatus = $selectedReception->getPaymentStatus();

                    // Payment status config
                    $paymentStatusConfig = match($paymentStatus) {
                        'PAYE' => [
                            'class' => 'success',
                            'label' => 'Payé',
                            'icon' => 'fa-check-circle'
                        ],
                        'PARTIEL' => [
                            'class' => 'warning',
                            'label' => 'Partiellement payé',
                            'icon' => 'fa-clock'
                        ],
                        'NON_PAYE' => [
                            'class' => 'danger',
                            'label' => 'Non payé',
                            'icon' => 'fa-times-circle'
                        ],
                        default => [
                            'class' => 'secondary',
                            'label' => $paymentStatus,
                            'icon' => 'fa-info-circle'
                        ],
                    };
                    @endphp

                    {{-- Financial Summary --}}
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Montant HT</div>
                                    <div class="fw-bold h4">
                                        {{ number_format($totalNoDiscount, 0, ',', ' ') }} {{ $currency }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Remise ({{ $discountPercentage }}%)</div>
                                    <div class="fw-bold h4 text-danger">
                                        - {{ number_format($discountAmount, 0, ',', ' ') }} {{ $currency }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Total net</div>
                                    <div class="fw-bold h4 text-success">
                                        {{ number_format($totalWithDiscount, 0, ',', ' ') }} {{ $currency }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Statut paiement</div>
                                    <div class="mt-2">
                                        <span class="badge bg-{{ $paymentStatusConfig['class'] }} bg-opacity-10 text-{{ $paymentStatusConfig['class'] }} border border-{{ $paymentStatusConfig['class'] }} border-opacity-25 p-2">
                                            <i class="fas {{ $paymentStatusConfig['icon'] }} me-1"></i> {{ $paymentStatusConfig['label'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Details --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-money-check me-2"></i>Total payé
                                    </h6>
                                    <div class="text-center">
                                        <div class="fw-bold h3 text-success">
                                            {{ number_format($totalPaid, 0, ',', ' ') }} {{ $currency }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $selectedReception->paiements->count() }} paiement(s)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-balance-scale me-2"></i>Reste à payer
                                    </h6>
                                    <div class="text-center">
                                        <div class="fw-bold h3 {{ $remainingAmount > 0 ? 'text-warning' : 'text-success' }}">
                                            {{ number_format($remainingAmount, 0, ',', ' ') }} {{ $currency }}
                                        </div>
                                        <small class="text-muted">
                                            @if($remainingAmount > 0)
                                                {{ number_format(($remainingAmount / $totalWithDiscount) * 100, 1) }}% du total
                                            @else
                                                Soldé
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-percentage me-2"></i>Avancement
                                    </h6>
                                    <div class="text-center">
                                        @php
                                            $progressPercentage = $totalWithDiscount > 0 ? ($totalPaid / $totalWithDiscount) * 100 : 0;
                                        @endphp
                                        <div class="fw-bold h3">{{ number_format($progressPercentage, 1) }}%</div>
                                        <div class="progress mt-2" style="height: 10px;">
                                            <div class="progress-bar bg-success"
                                                 role="progressbar"
                                                 style="width: {{ $progressPercentage }}%"
                                                 aria-valuenow="{{ $progressPercentage }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Commande & Fournisseur --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-shopping-cart me-2"></i>Commande
                                    </h6>
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <span class="text-muted small">Référence</span>
                                            <div class="fw-semibold">{{ $commande?->reference ?? '—' }}</div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <span class="text-muted small">Date</span>
                                            <div class="fw-semibold">
                                                {{ optional($commande?->date_commande)->format('d/m/Y') ?? '—' }}
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <span class="text-muted small">Devise</span>
                                            <div class="fw-semibold">{{ $currency }}</div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <span class="text-muted small">Remise</span>
                                            <div class="fw-semibold">{{ $discountPercentage }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-industry me-2"></i>Fournisseur
                                    </h6>
                                    <div class="fw-semibold h6 mb-2">
                                        {{ $commande?->fournisseur?->name ?? '—' }}
                                    </div>
                                    <div class="small">
                                        @if($commande?->fournisseur?->adresse)
                                            <div class="mb-1">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $commande->fournisseur->adresse }}
                                            </div>
                                        @endif
                                        @if($commande?->fournisseur?->telephone)
                                            <div class="mb-1">
                                                <i class="fas fa-phone me-1"></i>
                                                {{ $commande->fournisseur->telephone }}
                                            </div>
                                        @endif
                                        @if($commande?->fournisseur?->email)
                                            <div>
                                                <i class="fas fa-envelope me-1"></i>
                                                {{ $commande->fournisseur->email }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Reception Info --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Informations réception
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <span class="text-muted small">Date réception</span>
                                            <div class="fw-semibold">
                                                {{ \Carbon\Carbon::parse($selectedReception->date_reception)->format('d/m/Y') }}
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <span class="text-muted small">Articles reçus</span>
                                            <div class="fw-semibold">{{ $selectedReception->ligneReceptions->count() }}</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <span class="text-muted small">Quantité totale</span>
                                            <div class="fw-semibold">{{ $selectedReception->ligneReceptions->sum('quantity') }}</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <span class="text-muted small">Enregistré par</span>
                                            <div class="fw-semibold">{{ $selectedReception->createdBy?->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Articles réceptionnés --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-boxes me-2"></i>Articles réceptionnés
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Article</th>
                                        <th>Magasin</th>
                                        <th>Étagère</th>
                                        <th class="text-center">Qté</th>
                                        <th class="text-end">PU ({{ $currency }})</th>
                                        <th class="text-end">Total HT ({{ $currency }})</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $subtotal = 0;
                                    @endphp

                                    @foreach($selectedReception->ligneReceptions as $i => $ligne)
                                        @php
                                            $ligneCommande = $commande?->ligneCommandes
                                            ->firstWhere('article_id', $ligne->article_id);

                                            $pu = $ligneCommande?->unit_price ?? 0;
                                            $ligneTotal = $ligne->quantity * $pu;
                                            $subtotal += $ligneTotal;
                                        @endphp

                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <div class="fw-medium">{{ $ligne->article?->designation }}</div>
                                                <small class="text-muted">{{ $ligne->article?->reference }}</small>
                                            </td>
                                            <td>{{ $ligne->magasin?->nom ?? '—' }}</td>
                                            <td>{{ $ligne->etagere?->code_etagere ?? '—' }}</td>
                                            <td class="text-center">{{ $ligne->quantity }}</td>
                                            <td class="text-end">{{ number_format($pu, 2, ',', ' ') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($ligneTotal, 2, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    {{-- Subtotal --}}
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Sous-total</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($subtotal, 2, ',', ' ') }} {{ $currency }}
                                        </td>
                                    </tr>

                                    {{-- Discount --}}
                                    @if($discountPercentage > 0)
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold text-danger">
                                            Remise ({{ $discountPercentage }}%)
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            - {{ number_format($discountAmount, 2, ',', ' ') }} {{ $currency }}
                                        </td>
                                    </tr>
                                    @endif

                                    {{-- Total after discount --}}
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Total net</td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($totalWithDiscount, 2, ',', ' ') }} {{ $currency }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Paiements --}}
                    @if($selectedReception->paiements->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-credit-card me-2"></i>Historique des paiements
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Référence</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant ({{ $currency }})</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedReception->paiements as $i => $p)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}</td>
                                        <td class="fw-semibold">{{ $p->reference }}</td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                                {{ $modesPaiement[$p->mode_paiement] ?? $p->mode_paiement }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($p->montant, 2, ',', ' ') }}
                                        </td>
                                        <td class="small">
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $p->notes }}">
                                                {{ $p->notes }}
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total payé</td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($totalPaid, 2, ',', ' ') }} {{ $currency }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Aucun paiement enregistré pour cette réception.
                    </div>
                    @endif

                    {{-- Audit --}}
                    <div class="row pt-4 border-top">
                        <div class="col-md-6 small">
                            <div class="mb-1">
                                <span class="text-muted">Créé le:</span>
                                {{ \Carbon\Carbon::parse($selectedReception->created_at)->format('d/m/Y H:i') }}
                            </div>
                            <div>
                                <span class="text-muted">Par:</span>
                                {{ $selectedReception->createdBy?->name ?? '—' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-3">
                <button class="btn btn-secondary me-2"
                    wire:click="closeDetailsModal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button class="btn btn-primary"
                    onclick="printReceptionDetails()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>

        </div>
    </div>
</div>

@endif

