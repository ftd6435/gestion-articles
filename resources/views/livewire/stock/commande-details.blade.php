<div wire:show="showModalDetails"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white p-4"
                 style="background:linear-gradient(135deg,#4CAF50,#2E7D32)">
                <div>
                    <h5 class="fw-bold">
                        <i class="fas fa-file-invoice me-2"></i>Détails de la commande
                    </h5>
                    <small class="opacity-75">REF: {{ $selectedCommande->reference }}</small>
                </div>
                <button class="btn-close btn-close-white"
                        wire:click="closeDetails"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                <div class="p-4" id="printableArea">

                    {{-- Print-only header --}}
                    <div class="d-none d-print-block text-center mb-4">
                        <h2 class="mb-1">COMMANDE FOURNISSEUR</h2>
                        <p class="mb-0">REF: {{ $selectedCommande->reference }}</p>
                        <p class="mb-3">{{ \Carbon\Carbon::parse($selectedCommande->date_commande)->format('d/m/Y') }}</p>
                        <hr>
                    </div>

                    @php
                        // Get currency
                        $currency = $selectedCommande->devise?->symbole ?? $selectedCommande->devise?->code ?? 'FG';
                        $taux_change = $selectedCommande->taux_change ?? 1;

                        // Calculate totals
                        $subtotal = $selectedCommande->ligneCommandes->sum(function($ligne) {
                            return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
                        });

                        $totalQuantity = $selectedCommande->ligneCommandes->sum('quantity');

                        // Calculate received quantity
                        $totalReceived = 0;
                        $receivedByArticle = [];

                        if ($selectedCommande->receptions->count() > 0) {
                            foreach ($selectedCommande->receptions as $reception) {
                                foreach ($reception->ligneReceptions as $ligneReception) {
                                    $totalReceived += $ligneReception->quantity ?? 0;

                                    // Group by article
                                    $articleId = $ligneReception->article_id;
                                    if (!isset($receivedByArticle[$articleId])) {
                                        $receivedByArticle[$articleId] = 0;
                                    }
                                    $receivedByArticle[$articleId] += $ligneReception->quantity ?? 0;
                                }
                            }
                        }

                        $discountAmount = $subtotal * ($selectedCommande->remise / 100);
                        $totalAfterDiscount = $subtotal - $discountAmount;

                        // Calculate payment totals
                        $totalPaid = $selectedCommande->paiements->sum('montant');
                        $remainingAmount = $totalAfterDiscount - $totalPaid;
                        $paymentProgress = $totalAfterDiscount > 0 ? ($totalPaid / $totalAfterDiscount) * 100 : 0;

                        // Payment status
                        $paymentStatus = 'NON_PAYE';
                        if ($remainingAmount <= 0) {
                            $paymentStatus = 'PAYE';
                        } elseif ($totalPaid > 0) {
                            $paymentStatus = 'PARTIEL';
                        }

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

                        // Status badge config
                        $statusConfig = match($selectedCommande->status) {
                            'EN_COURS' => [
                                'class' => 'info',
                                'label' => 'En cours',
                                'icon' => 'fa-clock'
                            ],
                            'PARTIELLE' => [
                                'class' => 'warning',
                                'label' => 'Partielle',
                                'icon' => 'fa-truck-loading'
                            ],
                            'TERMINEE' => [
                                'class' => 'success',
                                'label' => 'Terminée',
                                'icon' => 'fa-check-circle'
                            ],
                            'ANNULEE' => [
                                'class' => 'danger',
                                'label' => 'Annulée',
                                'icon' => 'fa-ban'
                            ],
                            default => [
                                'class' => 'secondary',
                                'label' => $selectedCommande->status,
                                'icon' => 'fa-info-circle'
                            ],
                        };
                    @endphp

                    {{-- Header Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Fournisseur</h6>
                            <div class="fw-semibold">{{ $selectedCommande->fournisseur?->name }}</div>
                            <small class="text-muted">
                                {{ $selectedCommande->fournisseur?->telephone }} |
                                {{ $selectedCommande->fournisseur?->adresse }}
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="fw-bold mb-2">Informations</h6>
                            <div>
                                <span class="badge bg-{{ $statusConfig['class'] }} bg-opacity-10 text-{{ $statusConfig['class'] }} border border-{{ $statusConfig['class'] }} border-opacity-25">
                                    <i class="fas {{ $statusConfig['icon'] }} me-1"></i> {{ $statusConfig['label'] }}
                                </span>
                            </div>
                            <small class="text-muted">
                                Devise: {{ $currency }} |
                                Taux: {{ $taux_change }} |
                                Remise: {{ $selectedCommande->remise }}%
                            </small>
                        </div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body p-3">
                                    <div class="text-center">
                                        <div class="fw-bold h4 text-primary">{{ number_format($totalAfterDiscount, 0, ',', ' ') }} {{ $currency }}</div>
                                        <small class="text-muted">Montant net</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body p-3">
                                    <div class="text-center">
                                        <div class="fw-bold h4 text-success">{{ number_format($totalPaid, 0, ',', ' ') }} {{ $currency }}</div>
                                        <small class="text-muted">Total payé</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body p-3">
                                    <div class="text-center">
                                        <div class="fw-bold h4 {{ $remainingAmount > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($remainingAmount, 0, ',', ' ') }} {{ $currency }}</div>
                                        <small class="text-muted">Reste à payer</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border h-100">
                                <div class="card-body p-3">
                                    <div class="text-center">
                                        <span class="badge bg-{{ $paymentStatusConfig['class'] }} bg-opacity-10 text-{{ $paymentStatusConfig['class'] }} border border-{{ $paymentStatusConfig['class'] }} border-opacity-25 p-2">
                                            <i class="fas {{ $paymentStatusConfig['icon'] }} me-1"></i> {{ $paymentStatusConfig['label'] }}
                                        </span>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar bg-success"
                                                 role="progressbar"
                                                 style="width: {{ $paymentProgress }}%"
                                                 aria-valuenow="{{ $paymentProgress }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Reception Progress --}}
                    @if($selectedCommande->status !== 'ANNULEE')
                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-truck me-2"></i>État de réception
                            </h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="fw-bold fs-4 text-primary">{{ $totalQuantity }}</div>
                                        <small class="text-muted">Quantité commandée</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="fw-bold fs-4 text-success">{{ $totalReceived }}</div>
                                        <small class="text-muted">Quantité reçue</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        @php
                                            $pending = $totalQuantity - $totalReceived;
                                            $progress = $totalQuantity > 0 ? ($totalReceived / $totalQuantity) * 100 : 0;
                                        @endphp
                                        <div class="fw-bold fs-4 {{ $pending > 0 ? 'text-warning' : 'text-success' }}">
                                            {{ $pending }}
                                        </div>
                                        <small class="text-muted">En attente</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="fw-bold fs-4">{{ number_format($progress, 1) }}%</div>
                                        <small class="text-muted">Progression</small>
                                    </div>
                                </div>
                            </div>

                            @if($progress > 0)
                            <div class="mt-3">
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success"
                                         role="progressbar"
                                         style="width: {{ $progress }}%"
                                         aria-valuenow="{{ $progress }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Articles Table --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Article</th>
                                    <th class="text-center">Qté commandée</th>
                                    <th class="text-center">Qté reçue</th>
                                    <th class="text-center">En attente</th>
                                    <th class="text-end">PU ({{ $currency }})</th>
                                    <th class="text-end">Total ({{ $currency }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedCommande->ligneCommandes as $i => $ligne)
                                    @php
                                        $lineTotal = $ligne->quantity * $ligne->unit_price;
                                        $received = $receivedByArticle[$ligne->article_id] ?? 0;
                                        $pending = $ligne->quantity - $received;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $ligne->article?->reference }}</div>
                                            <small class="text-muted">{{ $ligne->article?->designation }}</small>
                                        </td>
                                        <td class="text-center fw-bold">{{ $ligne->quantity }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                {{ $received }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($pending > 0)
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                                    {{ $pending }}
                                                </span>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                    0
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($ligne->unit_price, 2, ',', ' ') }}
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($lineTotal, 2, ',', ' ') }}
                                        </td>
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
                                @if($selectedCommande->remise > 0)
                                <tr>
                                    <td colspan="6" class="text-end fw-bold text-danger">
                                        Remise ({{ $selectedCommande->remise }}%)
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        - {{ number_format($discountAmount, 2, ',', ' ') }} {{ $currency }}
                                    </td>
                                </tr>
                                @endif

                                {{-- Total after discount --}}
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Total net</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($totalAfterDiscount, 2, ',', ' ') }} {{ $currency }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Payment History --}}
                    @if($selectedCommande->paiements->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-money-check me-2"></i>Historique des paiements
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
                                    @foreach($selectedCommande->paiements as $i => $paiement)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                        <td class="fw-semibold">{{ $paiement->reference }}</td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                                {{ $paiement->mode_paiement }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($paiement->montant, 2, ',', ' ') }}
                                        </td>
                                        <td class="small">
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $paiement->notes }}">
                                                {{ $paiement->notes }}
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
                        Aucun paiement enregistré pour cette commande.
                    </div>
                    @endif

                    {{-- Reception History --}}
                    @if($selectedCommande->receptions->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-clipboard-list me-2"></i>Historique des réceptions
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <thead>
                                    <tr class="border-bottom">
                                        <th>Date</th>
                                        <th>Référence</th>
                                        <th>Articles reçus</th>
                                        <th class="text-end">Quantité totale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedCommande->receptions as $reception)
                                    <tr class="border-bottom">
                                        <td>
                                            {{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}
                                        </td>
                                        <td class="fw-semibold">
                                            {{ $reception->reference }}
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @php
                                                    $articles = [];
                                                    foreach ($reception->ligneReceptions as $ligne) {
                                                        $articles[] = $ligne->article?->reference . ' : ' . $ligne->article->designation . ' (' . $ligne->quantity . ')';
                                                    }
                                                @endphp
                                                {{ implode(', ', $articles) }}
                                            </small>
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ $reception->ligneReceptions->sum('quantity') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Aucune réception enregistrée pour cette commande.
                    </div>
                    @endif

                    {{-- Audit Info --}}
                    <div class="row border-top pt-3">
                        <div class="col-md-6">
                            <div class="mb-1">
                                <span class="small text-muted">Enregistré le: </span>
                                <span class="small fw-semibold text-muted">
                                    {{ \Carbon\Carbon::parse($selectedCommande->created_at)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div>
                                <span class="small text-muted">Par: </span>
                                <span class="small fw-semibold text-muted">{{ $selectedCommande->createdBy?->name ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="mb-1">
                                <span class="small text-muted">Date commande: </span>
                                <span class="small fw-semibold text-muted">
                                    {{ \Carbon\Carbon::parse($selectedCommande->date_commande)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Print-only footer --}}
                    <div class="d-none d-print-block mt-4 pt-3 border-top text-center small text-muted">
                        <p>Commande fournisseur - {{ config('app.name') }}</p>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-4">
                <button class="btn btn-secondary me-2"
                        wire:click="closeDetails">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button class="btn btn-outline-primary"
                        onclick="printCommande()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>

        </div>
    </div>
</div>
