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
                        Réception #{{ $selectedReception->id }}
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
                    @endphp

                    {{-- Summary --}}
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Total réception</div>
                                    <div class="fw-bold h4 text-info">
                                        {{ number_format($selectedReception->getTotalAmount(),0,',',' ') }} {{ $currency }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Total payé</div>
                                    <div class="fw-bold h4 text-success">
                                        {{ number_format($selectedReception->getTotalPaid(),0,',',' ') }} {{ $currency }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Reste à payer</div>
                                    <div class="fw-bold h4 text-warning">
                                        {{ number_format($selectedReception->getRemainingAmount(),0,',',' ') }} {{ $currency }}
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
                                    <div class="mb-2">
                                        <span class="text-muted small">Référence</span>
                                        <div class="fw-semibold">{{ $commande?->reference ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">Date</span>
                                        <div class="fw-semibold">
                                            {{ optional($commande?->date_commande)->format('d/m/Y') ?? '—' }}
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
                                    <div class="fw-semibold h6">
                                        {{ $commande?->fournisseur?->name ?? '—' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $commande?->fournisseur?->adresse ?? 'Aucune adresse' }}
                                        {{ $commande?->fournisseur?->telephone ? ' • '.$commande->fournisseur->telephone : '' }}
                                    </small>
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
                                        <th class="text-end">PU</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total = 0;
                                    @endphp

                                    @foreach($selectedReception->ligneReceptions as $i => $ligne)
                                        @php
                                            $ligneCommande = $commande?->ligneCommandes
                                            ->firstWhere('article_id', $ligne->article_id);

                                            $pu = $ligneCommande?->unit_price ?? 0;
                                            $ligneTotal = $ligne->quantity * $pu;
                                            $total += $ligneTotal;
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
                                            <td class="text-end">{{ number_format($pu,2,',',' ') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($ligneTotal,2,',',' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Total</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($total,2,',',' ') }} {{ $currency }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Paiements --}}
                    @if($selectedReception->paiements->count())
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-credit-card me-2"></i>Paiements liés
                        </h6>

                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Référence</th>
                                    <th>Mode</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedReception->paiements as $p)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}</td>
                                    <td>{{ $p->reference }}</td>
                                    <td>{{ $modesPaiement[$p->mode_paiement] ?? $p->mode_paiement }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($p->montant,0,',',' ') }} {{ $currency }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Audit --}}
                    <div class="row pt-4 border-top">
                        <div class="col-md-6 small">
                            <span class="text-muted">Créé le:</span>
                            {{ \Carbon\Carbon::parse($selectedReception->created_at)->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6 small text-end">
                            <span class="text-muted">Modifié le:</span>
                            {{ \Carbon\Carbon::parse($selectedReception->updated_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-3">
                <button class="btn btn-secondary me-2"
                    wire:click="closeDetailsModal">
                    Fermer
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
