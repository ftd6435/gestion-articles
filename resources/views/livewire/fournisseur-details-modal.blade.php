<div wire:show="showDetailsModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg" id="fournisseurDetails">
            {{-- Header --}}
            <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-industry me-2"></i>Profil du Fournisseur
                    </h5>
                    <div class="d-flex align-items-center mt-1">
                        @if($selectedFournisseur?->status)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-2">
                                <i class="fas fa-check-circle me-1"></i> Actif
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 me-2">
                                <i class="fas fa-times-circle me-1"></i> Inactif
                            </span>
                        @endif
                        <small class="text-white opacity-75">
                            ID: {{ $selectedFournisseur?->id }} • Créé le {{ $selectedFournisseur?->created_at?->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
                <button class="btn-close btn-close-white" wire:click="closeDetails"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                <div class="p-4">
                    @php
                        $fournisseur = $selectedFournisseur;
                        if(!$fournisseur) return;

                        // Get all receptions for this supplier
                        $allReceptions = $fournisseur->commandes->flatMap(fn($c) => $c->receptions);

                        // Calculate statistics using reception methods
                        $totalCommandes = $fournisseur->commandes->count();
                        $totalReceptions = $allReceptions->count();

                        // Get all payments from receptions
                        $allPaiements = $allReceptions->flatMap(fn($r) => $r->paiements);
                        $totalPaiements = $allPaiements->count();

                        // Calculate financial stats using reception methods
                        $totalMontantCommandesHT = $allReceptions->sum(fn($r) => $r->getTotalAmountNoDiscount());
                        $totalMontantCommandes = $allReceptions->sum(fn($r) => $r->getTotalAmount());
                        $totalRemise = $allReceptions->sum(fn($r) => $r->getDiscountAmount());
                        $totalMontantPaye = $allReceptions->sum(fn($r) => $r->getTotalPaid());
                        $totalResteAPayer = $allReceptions->sum(fn($r) => $r->getRemainingAmount());

                        // Payment status distribution
                        $receptionsPayees = $allReceptions->filter(fn($r) => $r->getPaymentStatus() === 'PAYE')->count();
                        $receptionsPartielles = $allReceptions->filter(fn($r) => $r->getPaymentStatus() === 'PARTIEL')->count();
                        $receptionsNonPayees = $allReceptions->filter(fn($r) => $r->getPaymentStatus() === 'NON_PAYE')->count();

                        // Average per reception
                        $avgMontantReception = $totalReceptions > 0 ? $totalMontantCommandes / $totalReceptions : 0;
                        $avgPaiementPerReception = $totalReceptions > 0 ? $totalMontantPaye / $totalReceptions : 0;

                        // Payment completion rate based on receptions
                        $receptionsCompletes = $receptionsPayees;
                        $completionRate = $totalReceptions > 0 ? ($receptionsCompletes / $totalReceptions) * 100 : 0;

                        // Recent activity (last 30 days)
                        $thirtyDaysAgo = now()->subDays(30);
                        $recentCommandes = $fournisseur->commandes->where('created_at', '>=', $thirtyDaysAgo)->count();
                        $recentPaiements = $allPaiements->where('date_paiement', '>=', $thirtyDaysAgo)->count();

                        // Calculate average discount percentage
                        $avgRemisePourcentage = $totalMontantCommandesHT > 0 ? ($totalRemise / $totalMontantCommandesHT) * 100 : 0;
                    @endphp

                    {{-- Profile Info Row --}}
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="avatar-circle mb-3">
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="fas fa-user text-white fa-3x text-primary"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold">{{ $fournisseur->name }}</h5>
                            <div class="small text-muted">Fournisseur</div>
                        </div>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="fas fa-address-card me-2"></i>Coordonnées
                                            </h6>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Téléphone</span>
                                                <div class="fw-semibold">
                                                    <i class="fas fa-phone me-2 text-primary"></i>
                                                    {{ $fournisseur->telephone }}
                                                </div>
                                            </div>
                                            @if($fournisseur->adresse)
                                            <div>
                                                <span class="text-muted small d-block">Adresse</span>
                                                <div class="fw-semibold">
                                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                    {{ $fournisseur->adresse }}
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="fas fa-chart-line me-2"></i>Activité récente (30 jours)
                                            </h6>
                                            <div class="mb-2">
                                                <span class="text-muted small">Nouvelles commandes</span>
                                                <div class="fw-bold">{{ $recentCommandes }}</div>
                                            </div>
                                            <div>
                                                <span class="text-muted small">Paiements reçus</span>
                                                <div class="fw-bold">{{ $recentPaiements }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Statistics Cards --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-shopping-cart me-1"></i> Commandes
                                    </div>
                                    <div class="fw-bold h3 text-primary">{{ $totalCommandes }}</div>
                                    <div class="small text-muted">Total</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-truck me-1"></i> Réceptions
                                    </div>
                                    <div class="fw-bold h3 text-info">{{ $totalReceptions }}</div>
                                    <div class="small text-muted">
                                        @if($totalCommandes > 0)
                                            {{ round(($totalReceptions / $totalCommandes) * 100, 1) }}% des commandes
                                        @else
                                            Aucune commande
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-credit-card me-1"></i> Paiements
                                    </div>
                                    <div class="fw-bold h3 text-success">{{ $totalPaiements }}</div>
                                    <div class="small text-muted">
                                        @if($totalReceptions > 0)
                                            {{ round(($totalPaiements / $totalReceptions) * 100, 1) }} paiements/rcpt
                                        @else
                                            Aucune réception
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-percentage me-1"></i> Taux de paiement
                                    </div>
                                    <div class="fw-bold h3 {{ $completionRate >= 80 ? 'text-success' : ($completionRate >= 50 ? 'text-warning' : 'text-danger') }}">
                                        {{ number_format($completionRate, 1) }}%
                                    </div>
                                    <div class="small text-muted">Réceptions payées</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Status Distribution --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-chart-pie me-2"></i>Statut des paiements
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 p-2">
                                                    <i class="fas fa-check-circle me-1"></i> Payées
                                                </span>
                                            </div>
                                            <div class="fw-bold h4 text-success">{{ $receptionsPayees }}</div>
                                            <div class="small text-muted">
                                                @if($totalReceptions > 0)
                                                    {{ round(($receptionsPayees / $totalReceptions) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 p-2">
                                                    <i class="fas fa-clock me-1"></i> Partielles
                                                </span>
                                            </div>
                                            <div class="fw-bold h4 text-warning">{{ $receptionsPartielles }}</div>
                                            <div class="small text-muted">
                                                @if($totalReceptions > 0)
                                                    {{ round(($receptionsPartielles / $totalReceptions) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 p-2">
                                                    <i class="fas fa-times-circle me-1"></i> Non payées
                                                </span>
                                            </div>
                                            <div class="fw-bold h4 text-danger">{{ $receptionsNonPayees }}</div>
                                            <div class="small text-muted">
                                                @if($totalReceptions > 0)
                                                    {{ round(($receptionsNonPayees / $totalReceptions) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-money-bill-wave me-2"></i>Chiffre d'affaires
                                    </h6>
                                    <div class="mb-2">
                                        <span class="text-muted small">Montant HT total</span>
                                        <div class="fw-bold h5 text-primary">
                                            {{ number_format($totalMontantCommandesHT, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-muted small">Remise totale ({{ number_format($avgRemisePourcentage, 1) }}%)</span>
                                        <div class="fw-bold h5 text-danger">
                                            - {{ number_format($totalRemise, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div class="mb-3 border-bottom pb-3">
                                        <span class="text-muted small">Montant net total</span>
                                        <div class="fw-bold h4 text-success">
                                            {{ number_format($totalMontantCommandes, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-muted small">Montant total payé</span>
                                        <div class="fw-bold h5 text-success">
                                            {{ number_format($totalMontantPaye, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">Reste total à payer</span>
                                        <div class="fw-bold h4 {{ $totalResteAPayer > 0 ? 'text-warning' : 'text-success' }}">
                                            {{ number_format($totalResteAPayer, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-chart-bar me-2"></i>Moyennes & Progression
                                    </h6>
                                    <div class="mb-3">
                                        <span class="text-muted small">Montant moyen par réception</span>
                                        <div class="fw-bold h5">
                                            {{ number_format($avgMontantReception, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="text-muted small">Paiement moyen par réception</span>
                                        <div class="fw-bold h5">
                                            {{ number_format($avgPaiementPerReception, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">Progression des paiements</span>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 me-3">
                                                <div class="progress" style="height: 10px;">
                                                    @php
                                                        $payePercentage = $totalMontantCommandes > 0 ? ($totalMontantPaye / $totalMontantCommandes) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-success"
                                                         role="progressbar"
                                                         style="width: {{ $payePercentage }}%"
                                                         aria-valuenow="{{ $payePercentage }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="fw-bold">{{ number_format($payePercentage, 1) }}%</div>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            {{ number_format($totalMontantPaye, 0, ',', ' ') }} FG / {{ number_format($totalMontantCommandes, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Receptions --}}
                    @if($allReceptions->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-truck-loading me-2"></i>Dernières réceptions
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Référence</th>
                                        <th>Date</th>
                                        <th class="text-end">Montant net</th>
                                        <th class="text-end">Payé</th>
                                        <th class="text-end">Reste</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allReceptions->sortByDesc('date_reception')->take(5) as $reception)
                                    @php
                                        $montantNet = $reception->getTotalAmount();
                                        $montantPaye = $reception->getTotalPaid();
                                        $reste = $reception->getRemainingAmount();
                                        $paymentStatus = $reception->getPaymentStatus();
                                        $statusColor = $paymentStatus === 'PAYE' ? 'success' :
                                                      ($paymentStatus === 'PARTIEL' ? 'warning' : 'danger');
                                        $statusLabel = $paymentStatus === 'PAYE' ? 'Payé' :
                                                      ($paymentStatus === 'PARTIEL' ? 'Partiel' : 'Non payé');
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $reception->reference }}</td>
                                        <td>{{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($montantNet, 0, ',', ' ') }} FG</td>
                                        <td class="text-end text-success">{{ number_format($montantPaye, 0, ',', ' ') }} FG</td>
                                        <td class="text-end {{ $reste > 0 ? 'text-warning' : 'text-success' }}">
                                            {{ number_format($reste, 0, ',', ' ') }} FG
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                @if($allReceptions->count() > 5)
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-center small text-muted">
                                            Affichage des 5 dernières réceptions sur {{ $totalReceptions }} au total
                                        </td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Recent Payments --}}
                    @if($totalPaiements > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-credit-card me-2"></i>Derniers paiements
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Référence</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Réception</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $recentPayments = $allPaiements->sortByDesc('date_paiement')->take(5);
                                    @endphp
                                    @foreach($recentPayments as $paiement)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                        <td class="fw-semibold">{{ $paiement->reference }}</td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ $paiement->mode_paiement }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($paiement->montant, 0, ',', ' ') }} FG
                                        </td>
                                        <td>{{ $paiement->reception?->reference ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                @if($totalPaiements > 5)
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-center small text-muted">
                                            Affichage des 5 derniers paiements sur {{ $totalPaiements }} au total
                                        </td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Audit Trail --}}
                    <div class="row mt-4 pt-4 border-top">
                        <div class="col-md-6">
                            <div>
                                <span class="text-muted small">Créé le: </span>
                                <span class="fw-semibold small">{{ $fournisseur->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($fournisseur->createdBy)
                            <div>
                                <span class="text-muted small">Par: </span>
                                <span class="fw-semibold small">{{ $fournisseur->createdBy->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-3">
                <button class="btn btn-secondary me-2" wire:click="closeDetails">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                @if($selectedFournisseur)
                <a href="{{ route('stock.commandes') }}" class="btn btn-primary me-2">
                    <i class="fas fa-shopping-cart me-2"></i>Voir commandes
                </a>
                <button class="btn btn-outline-primary" onclick="printFournisseurDetails()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        margin: 0 auto;
        color: white;
    }

    .modal-header .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
