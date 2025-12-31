<div wire:show="showDetailsModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg" id="clientDetails">
            {{-- Header --}}
            <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user-tie me-2"></i>Profil du Client
                    </h5>
                    <div class="d-flex align-items-center mt-1">
                        @if($selectedClient?->status)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-2">
                                <i class="fas fa-check-circle me-1"></i> Actif
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 me-2">
                                <i class="fas fa-times-circle me-1"></i> Inactif
                            </span>
                        @endif
                        @php
                            $typeColors = [
                                'GROSSISTE' => 'primary',
                                'DETAILLANT' => 'info',
                                'MIXTE' => 'warning'
                            ];
                            $typeColor = $typeColors[$selectedClient?->type] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $typeColor }} bg-opacity-10 text-{{ $typeColor }} border border-{{ $typeColor }} border-opacity-25 me-2">
                            {{ $selectedClient?->type }}
                        </span>
                        <small class="text-white opacity-75">
                            ID: {{ $selectedClient?->id }} • Créé le {{ $selectedClient?->created_at?->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
                <button class="btn-close btn-close-white" wire:click="closeDetails"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                <div class="p-4">
                    @php
                        $client = $selectedClient;
                        if(!$client) return;

                        // Get all sales for this client
                        $allVentes = $client->ventes;

                        // Calculate statistics using vente methods
                        $totalVentes = $allVentes->count();

                        // Get all payments from sales
                        $allPaiements = $allVentes->flatMap(fn($v) => $v->paiements);
                        $totalPaiements = $allPaiements->count();

                        // Calculate financial stats using vente methods
                        $totalMontantVentesHT = $allVentes->sum(fn($v) => $v->subTotal());
                        $totalMontantVentes = $allVentes->sum(fn($v) => $v->totalAfterRemise());
                        $totalRemise = $allVentes->sum(fn($v) => $v->discountAmount());
                        $totalMontantPaye = $allVentes->sum(fn($v) => $v->totalPaid());
                        $totalResteAPayer = $allVentes->sum(fn($v) => $v->remainingAmount());

                        // Payment status distribution
                        $ventesPayees = $allVentes->filter(fn($v) => $v->remainingAmount() <= 0)->count();
                        $ventesPartielles = $allVentes->filter(fn($v) => $v->totalPaid() > 0 && $v->remainingAmount() > 0)->count();
                        $ventesNonPayees = $allVentes->filter(fn($v) => $v->totalPaid() <= 0)->count();

                        // Average per vente
                        $avgMontantVente = $totalVentes > 0 ? $totalMontantVentes / $totalVentes : 0;
                        $avgPaiementPerVente = $totalVentes > 0 ? $totalMontantPaye / $totalVentes : 0;

                        // Payment completion rate based on sales
                        $ventesCompletes = $ventesPayees;
                        $completionRate = $totalVentes > 0 ? ($ventesCompletes / $totalVentes) * 100 : 0;

                        // Recent activity (last 30 days)
                        $thirtyDaysAgo = now()->subDays(30);
                        $recentVentes = $client->ventes->where('created_at', '>=', $thirtyDaysAgo)->count();
                        $recentPaiements = $allPaiements->where('created_at', '>=', $thirtyDaysAgo)->count();

                        // Calculate average discount percentage
                        $avgRemisePourcentage = $totalMontantVentesHT > 0 ? ($totalRemise / $totalMontantVentesHT) * 100 : 0;
                    @endphp

                    {{-- Profile Info Row --}}
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="avatar-circle mb-3">
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold">{{ $client->name }}</h5>
                            <div class="small text-muted">Client {{ $client->type }}</div>
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
                                                    {{ $client->telephone }}
                                                </div>
                                            </div>
                                            @if($client->email)
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Email</span>
                                                <div class="fw-semibold">
                                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                                    {{ $client->email }}
                                                </div>
                                            </div>
                                            @endif
                                            @if($client->adresse)
                                            <div>
                                                <span class="text-muted small d-block">Adresse</span>
                                                <div class="fw-semibold">
                                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                    {{ $client->adresse }}
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
                                                <span class="text-muted small">Nouvelles ventes</span>
                                                <div class="fw-bold">{{ $recentVentes }}</div>
                                            </div>
                                            <div>
                                                <span class="text-muted small">Paiements effectués</span>
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
                                        <i class="fas fa-shopping-cart me-1"></i> Ventes
                                    </div>
                                    <div class="fw-bold h3 text-primary">{{ $totalVentes }}</div>
                                    <div class="small text-muted">Total</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-cash-register me-1"></i> Chiffre d'affaires
                                    </div>
                                    <div class="fw-bold h3 text-success">
                                        {{ number_format($totalMontantVentes/1000000, 1) }}M
                                    </div>
                                    <div class="small text-muted">FG</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-credit-card me-1"></i> Paiements
                                    </div>
                                    <div class="fw-bold h3 text-info">{{ $totalPaiements }}</div>
                                    <div class="small text-muted">
                                        @if($totalVentes > 0)
                                            {{ round(($totalPaiements / $totalVentes) * 100, 1) }} paiements/vente
                                        @else
                                            Aucune vente
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-percentage me-1"></i> Taux de règlement
                                    </div>
                                    <div class="fw-bold h3 {{ $completionRate >= 80 ? 'text-success' : ($completionRate >= 50 ? 'text-warning' : 'text-danger') }}">
                                        {{ number_format($completionRate, 1) }}%
                                    </div>
                                    <div class="small text-muted">Ventes réglées</div>
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
                                        <i class="fas fa-chart-pie me-2"></i>Statut des règlements
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 p-2">
                                                    <i class="fas fa-check-circle me-1"></i> Réglées
                                                </span>
                                            </div>
                                            <div class="fw-bold h4 text-success">{{ $ventesPayees }}</div>
                                            <div class="small text-muted">
                                                @if($totalVentes > 0)
                                                    {{ round(($ventesPayees / $totalVentes) * 100, 1) }}%
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
                                            <div class="fw-bold h4 text-warning">{{ $ventesPartielles }}</div>
                                            <div class="small text-muted">
                                                @if($totalVentes > 0)
                                                    {{ round(($ventesPartielles / $totalVentes) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 p-2">
                                                    <i class="fas fa-times-circle me-1"></i> Impayées
                                                </span>
                                            </div>
                                            <div class="fw-bold h4 text-danger">{{ $ventesNonPayees }}</div>
                                            <div class="small text-muted">
                                                @if($totalVentes > 0)
                                                    {{ round(($ventesNonPayees / $totalVentes) * 100, 1) }}%
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
                                        <i class="fas fa-money-bill-wave me-2"></i>Récapitulatif financier
                                    </h6>
                                    <div class="mb-2">
                                        <span class="text-muted small">Montant HT total</span>
                                        <div class="fw-bold h5 text-primary">
                                            {{ number_format($totalMontantVentesHT, 0, ',', ' ') }} FG
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
                                            {{ number_format($totalMontantVentes, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-muted small">Montant total payé</span>
                                        <div class="fw-bold h5 text-success">
                                            {{ number_format($totalMontantPaye, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">Solde à recevoir</span>
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
                                        <span class="text-muted small">Montant moyen par vente</span>
                                        <div class="fw-bold h5">
                                            {{ number_format($avgMontantVente, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="text-muted small">Paiement moyen par vente</span>
                                        <div class="fw-bold h5">
                                            {{ number_format($avgPaiementPerVente, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">Progression des règlements</span>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 me-3">
                                                <div class="progress" style="height: 10px;">
                                                    @php
                                                        $payePercentage = $totalMontantVentes > 0 ? ($totalMontantPaye / $totalMontantVentes) * 100 : 0;
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
                                            {{ number_format($totalMontantPaye, 0, ',', ' ') }} FG / {{ number_format($totalMontantVentes, 0, ',', ' ') }} FG
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Sales --}}
                    @if($allVentes->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-receipt me-2"></i>Dernières ventes
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
                                    @foreach($allVentes->sortByDesc('created_at')->take(5) as $vente)
                                    @php
                                        $montantNet = $vente->totalAfterRemise();
                                        $montantPaye = $vente->totalPaid();
                                        $reste = $vente->remainingAmount();
                                        $paymentStatus = $reste <= 0 ? 'PAYE' : ($montantPaye > 0 ? 'PARTIEL' : 'NON_PAYE');
                                        $statusColor = $paymentStatus === 'PAYE' ? 'success' :
                                                      ($paymentStatus === 'PARTIEL' ? 'warning' : 'danger');
                                        $statusLabel = $paymentStatus === 'PAYE' ? 'Payé' :
                                                      ($paymentStatus === 'PARTIEL' ? 'Partiel' : 'Non payé');
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $vente->reference }}</td>
                                        <td>{{ \Carbon\Carbon::parse($vente->date_facture)->format('d/m/Y') }}</td>
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
                                @if($allVentes->count() > 5)
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-center small text-muted">
                                            Affichage des 5 dernières ventes sur {{ $totalVentes }} au total
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
                                        <th>Vente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $recentPayments = $allPaiements->sortByDesc('created_at')->take(5);
                                    @endphp
                                    @foreach($recentPayments as $paiement)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($paiement->created_at)->format('d/m/Y') }}</td>
                                        <td class="fw-semibold">{{ $paiement->reference ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ $paiement->mode_paiement ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($paiement->montant, 0, ',', ' ') }} FG
                                        </td>
                                        <td>{{ $paiement->vente?->reference ?? '—' }}</td>
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
                                <span class="fw-semibold small">{{ $client->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($client->createdBy)
                            <div>
                                <span class="text-muted small">Par: </span>
                                <span class="fw-semibold small">{{ $client->createdBy->name }}</span>
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
                @if($selectedClient)
                <button class="btn btn-outline-primary" onclick="printClientDetails()">
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
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        margin: 0 auto;
        color: white;
    }

    .modal-header .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>

