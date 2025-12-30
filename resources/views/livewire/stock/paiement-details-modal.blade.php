<div wire:show="showDetailsModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">
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
            <div class="modal-body bg-white">
                <div class="p-4">
                    @php
                        // Calculate financial details
                        $reception = $selectedPaiement->reception;
                        $currency = $selectedPaiement->commande?->devise?->symbole ?? $selectedPaiement->commande?->devise?->code ?? 'FG';
                        $remisePourcentage = $selectedPaiement->commande?->remise ?? 0;

                        // Calculate amounts if reception exists
                        $montantHT = $reception ? $reception->getTotalAmountNoDiscount() : 0;
                        $montantRemise = $reception ? $reception->getDiscountAmount() : 0;
                        $montantNet = $reception ? $reception->getTotalAmount() : 0;
                        $totalPaye = $reception ? $reception->getTotalPaid() : 0;
                        $resteAPayer = $reception ? $reception->getRemainingAmount() : 0;
                        $statutPaiement = $reception ? $reception->getPaymentStatus() : 'NON_PAYE';

                        // Payment status config
                        $paymentStatusConfig = match($statutPaiement) {
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
                                'label' => $statutPaiement,
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
                                        {{ number_format($montantHT, 0, ',', ' ') }} {{ $currency }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Remise ({{ $remisePourcentage }}%)</div>
                                    <div class="fw-bold h4 text-danger">
                                        - {{ number_format($montantRemise, 0, ',', ' ') }} {{ $currency }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Total net</div>
                                    <div class="fw-bold h4 text-success">
                                        {{ number_format($montantNet, 0, ',', ' ') }} {{ $currency }}
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

                    {{-- Payment Progress --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-money-check me-2"></i>Total payé
                                    </h6>
                                    <div class="text-center">
                                        <div class="fw-bold h3 text-success">
                                            {{ number_format($totalPaye, 0, ',', ' ') }} {{ $currency }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $reception?->paiements->count() ?? 0 }} paiement(s)
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
                                        <div class="fw-bold h3 {{ $resteAPayer > 0 ? 'text-warning' : 'text-success' }}">
                                            {{ number_format($resteAPayer, 0, ',', ' ') }} {{ $currency }}
                                        </div>
                                        <small class="text-muted">
                                            @if($montantNet > 0)
                                                @if($resteAPayer > 0)
                                                    {{ number_format(($resteAPayer / $montantNet) * 100, 1) }}% du total
                                                @else
                                                    Soldé
                                                @endif
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
                                            $progressPercentage = $montantNet > 0 ? ($totalPaye / $montantNet) * 100 : 0;
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

                    {{-- Header Info --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small mb-1">Fournisseur</div>
                                    <div class="fw-bold h6">{{ $selectedPaiement->commande?->fournisseur?->name ?? '—' }}</div>
                                    <small class="text-muted">
                                        {{ $selectedPaiement->commande?->fournisseur?->adresse ?? '' }}
                                        {{ $selectedPaiement->commande?->fournisseur?->telephone ? ' • ' . $selectedPaiement->commande?->fournisseur?->telephone : '' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small mb-1">Montant payé</div>
                                    <div class="fw-bold h4 text-success">
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
                                            $color = $modeColors[$selectedPaiement->mode_paiement] ?? 'secondary';
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
                                        <div class="col-6 mt-2">
                                            <div class="text-muted small">Devise</div>
                                            <div class="fw-semibold">
                                                {{ $selectedPaiement->commande?->devise?->code ?? '—' }}
                                                ({{ $selectedPaiement->commande?->devise?->symbole ?? '' }})
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="text-muted small">Remise</div>
                                            <div class="fw-semibold">{{ $remisePourcentage }}%</div>
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
                                            <div class="fw-semibold">{{ $selectedPaiement->reception?->reference ?? '—' }}</div>
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
                                            {{ number_format($montantNet, 0, ',', ' ') }} {{ $currency }}
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
                                            <th class="text-end">Total HT</th>
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
                                            <td colspan="4" class="text-end fw-bold">Sous-total:</td>
                                            <td class="text-end fw-bold">{{ number_format($totalArticles, 2, ',', ' ') }} {{ $currency }}</td>
                                        </tr>
                                        @if($remisePourcentage > 0)
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold text-danger">
                                                Remise ({{ $remisePourcentage }}%)
                                            </td>
                                            <td class="text-end fw-bold text-danger">
                                                - {{ number_format($montantRemise, 2, ',', ' ') }} {{ $currency }}
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Total net:</td>
                                            <td class="text-end fw-bold text-success">
                                                {{ number_format($montantNet, 2, ',', ' ') }} {{ $currency }}
                                            </td>
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
                                        <tr class="table-light">
                                            <td colspan="3" class="text-end fw-bold">Total payé:</td>
                                            <td class="text-end fw-bold text-success">{{ number_format($totalPaye, 2, ',', ' ') }} {{ $currency }}</td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-light">
                                            <td colspan="3" class="text-end fw-bold">Total réception:</td>
                                            <td class="text-end fw-bold">{{ number_format($montantNet, 2, ',', ' ') }} {{ $currency }}</td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-light">
                                            <td colspan="3" class="text-end fw-bold">Reste à payer:</td>
                                            <td class="text-end fw-bold {{ $resteAPayer > 0 ? 'text-warning' : 'text-success' }}">
                                                {{ number_format($resteAPayer, 2, ',', ' ') }} {{ $currency }}
                                            </td>
                                            <td>
                                                @if($resteAPayer <= 0)
                                                    <span class="badge bg-success">Payé</span>
                                                @elseif($resteAPayer < $montantNet)
                                                    <span class="badge bg-warning">Partiel</span>
                                                @else
                                                    <span class="badge bg-danger">Impayé</span>
                                                @endif
                                            </td>
                                        </tr>
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
                            <div class="text-muted small">Créé le {{ $selectedPaiement->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                            <div>
                                <span class="text-muted small">Par: </span>
                                <span class="fw-semibold small">{{ $selectedPaiement->createdBy?->name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-4" style="background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-secondary me-2" wire:click="closeDetailsModal">
                            <i class="fas fa-times me-2"></i>Fermer
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" onclick="printPaiement()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
