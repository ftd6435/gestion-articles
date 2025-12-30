<div wire:show="showDetailsModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white p-4"
                 style="background:linear-gradient(135deg,#667eea,#764ba2)">
                <div>
                    <h5 class="fw-bold">
                        <i class="fas fa-receipt me-2"></i>Détails de la vente
                    </h5>
                    <small class="opacity-75">REF: {{ $selectedVente->reference }}</small>
                </div>
                <button class="btn-close btn-close-white"
                        wire:click="closeDetailsModal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                <div class="p-4" id="printableArea">

                    {{-- Print-only header --}}
                    <div class="d-none d-print-block text-center mb-4">
                        <h2 class="mb-1">FACTURE DE VENTE</h2>
                        <p class="mb-0">REF: {{ $selectedVente->reference }}</p>
                        <p class="mb-3">{{ \Carbon\Carbon::parse($selectedVente->date_facture)->format('d/m/Y') }}</p>
                        <hr>
                    </div>

                    @php
                        $currency = $selectedVente->devise?->symbole
                            ?? $selectedVente->devise?->code
                            ?? 'FG';

                        // Calculate totals
                        $subtotal = $selectedVente->ligneVentes->sum(fn($l) => ($l->quantity ?? 0) * ($l->unit_price ?? 0));
                        $discountAmount = $subtotal * ($selectedVente->remise / 100);
                        $totalAfterDiscount = $subtotal - $discountAmount;

                        // Get payments
                        $totalPaid = $selectedVente->paiements()->sum('montant');
                        $remaining = max(0, $totalAfterDiscount - $totalPaid);

                        // Status badge config
                        $statusConfig = match($selectedVente->status) {
                            'PAYEE' => [
                                'class' => 'success',
                                'label' => 'Payée',
                                'icon' => 'fa-check-circle'
                            ],
                            'PARTIELLE' => [
                                'class' => 'warning',
                                'label' => 'Partielle',
                                'icon' => 'fa-exclamation-circle'
                            ],
                            'IMPAYEE' => [
                                'class' => 'danger',
                                'label' => 'Impayée',
                                'icon' => 'fa-times-circle'
                            ],
                            'ANNULEE' => [
                                'class' => 'secondary',
                                'label' => 'Annulée',
                                'icon' => 'fa-ban'
                            ],
                            default => [
                                'class' => 'info',
                                'label' => $selectedVente->status,
                                'icon' => 'fa-info-circle'
                            ],
                        };
                    @endphp

                    {{-- Header Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Client</h6>
                            <div class="fw-semibold">{{ $selectedVente->client?->name }}</div>
                            <small class="text-muted">{{ $selectedVente->client?->telephone }} | {{ $selectedVente->client?->adresse }}</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="fw-bold mb-2">Informations</h6>
                            <div>
                                <span class="badge bg-{{ $statusConfig['class'] }} bg-opacity-10 text-{{ $statusConfig['class'] }} border border-{{ $statusConfig['class'] }} border-opacity-25">
                                    <i class="fas {{ $statusConfig['icon'] }} me-1"></i> {{ $statusConfig['label'] }}
                                </span>
                            </div>
                            <small class="text-muted">
                                Type: {{ $selectedVente->type_vente }} |
                                Devise: {{ $currency }}
                            </small>
                        </div>
                    </div>

                    {{-- Articles Table --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Article</th>
                                    <th class="text-center">Qté</th>
                                    <th class="text-end">PU</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedVente->ligneVentes as $i => $ligne)
                                    @php
                                        $lineTotal = $ligne->quantity * $ligne->unit_price;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $ligne->article?->designation }}</td>
                                        <td class="text-center">{{ $ligne->quantity }}</td>
                                        <td class="text-end">
                                            {{ number_format($ligne->unit_price,2,',',' ') }}
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($lineTotal,2,',',' ') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                {{-- Subtotal --}}
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Sous-total</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($subtotal,2,',',' ') }} {{ $currency }}
                                    </td>
                                </tr>

                                {{-- Discount --}}
                                @if($selectedVente->remise > 0)
                                <tr>
                                    <td colspan="4" class="text-end fw-bold text-danger">
                                        Remise ({{ $selectedVente->remise }}%)
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        - {{ number_format($discountAmount,2,',',' ') }} {{ $currency }}
                                    </td>
                                </tr>
                                @endif

                                {{-- Total after discount --}}
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total net</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($totalAfterDiscount,2,',',' ') }} {{ $currency }}
                                    </td>
                                </tr>

                                {{-- Paid amount --}}
                                <tr>
                                    <td colspan="4" class="text-end fw-bold text-success">
                                        <i class="fas fa-money-bill-wave me-1"></i> Montant payé
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ number_format($totalPaid,2,',',' ') }} {{ $currency }}
                                    </td>
                                </tr>

                                {{-- Remaining --}}
                                <tr>
                                    <td colspan="4" class="text-end fw-bold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                        <i class="fas fa-clock me-1"></i> Reste à payer
                                    </td>
                                    <td class="text-end fw-bold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($remaining,2,',',' ') }} {{ $currency }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Payment History --}}
                    @if($selectedVente->paiements->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Historique des paiements</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <thead>
                                    <tr class="border-bottom">
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Référence</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedVente->paiements as $paiement)
                                    <tr class="border-bottom">
                                        <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $paiement->mode_paiement }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($paiement->montant,2,',',' ') }} {{ $currency }}
                                        </td>
                                        <td class="small text-muted">{{ $paiement->reference }}</td>
                                        <td class="small text-muted">{{ $paiement->notes ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Audit Info --}}
                    <div class="row border-top pt-3">
                        <div class="col-md-6">
                            <span class="small text-muted">Enrégistré par: </span>
                            <span class="small fw-semibold text-muted">{{ $selectedVente->createdBy?->name }}</span>
                            <br>
                            @if($selectedVente->updatedBy)
                            <span class="small text-muted">Modifié par: </span>
                            <span class="small fw-semibold text-muted">{{ $selectedVente->updatedBy?->name }}</span>
                            @endif
                        </div>
                        <div class="col-md-6 text-end small text-muted">
                            Date: {{ \Carbon\Carbon::parse($selectedVente->date_facture)->format('d/m/Y H:i') }}
                            <br>
                            Type: {{ $selectedVente->type_vente }}
                            <br>
                            Remise: {{ $selectedVente->remise }}%
                        </div>
                    </div>

                    {{-- Print-only footer --}}
                    <div class="d-none d-print-block mt-4 pt-3 border-top text-center small text-muted">
                        <p>Merci pour votre fidélité !</p>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-4">
                <button class="btn btn-secondary me-2"
                        wire:click="closeDetailsModal">
                    Fermer
                </button>
                <button class="btn btn-primary"
                        onclick="printModal()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>

        </div>
    </div>
</div>
