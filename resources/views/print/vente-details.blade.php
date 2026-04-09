@php
    $showHeader = $showHeader ?? false;
    $company = \App\Models\CompanySetting::query()->first();
    $companyName = $company?->name ?: config('app.name');
    $companyTel = $company?->telephone;
    $companyEmail = $company?->email;
    $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;
    $publicUrl = \Illuminate\Support\Facades\URL::signedRoute('public.ventes.show', [
        'vente' => $selectedVente->id,
    ]);
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' . urlencode($publicUrl);
@endphp

<div class="p-4" id="printableArea">
    <div class="{{ $showHeader ? '' : 'd-none d-print-block' }} mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3">
                @if($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="Logo" style="max-height: 60px; max-width: 160px;">
                @endif
                <div>
                    <div class="fw-bold fs-5">{{ $companyName }}</div>
                    <div class="small text-muted">
                        @if($companyTel)
                            <span>{{ $companyTel }}</span>
                        @endif
                        @if($companyTel && $companyEmail)
                            <span class="mx-2">|</span>
                        @endif
                        @if($companyEmail)
                            <span>{{ $companyEmail }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-end">
                <img src="{{ $qrUrl }}" alt="QR" style="height: 90px; width: 90px;">
                <div class="small text-muted mt-1">Scanner pour ouvrir</div>
            </div>
        </div>

        <div class="text-center mt-3">
            <h2 class="mb-1">FACTURE DE VENTE</h2>
            <div class="mb-0">REF: {{ $selectedVente->reference }}</div>
            <div class="mb-0">{{ \Carbon\Carbon::parse($selectedVente->date_facture)->format('d/m/Y') }}</div>
        </div>
        <hr class="mt-3 mb-0">
    </div>

    @php
        $currency = $selectedVente->devise?->symbole
            ?? $selectedVente->devise?->code
            ?? 'FG';

        $subtotal = $selectedVente->ligneVentes->sum(fn($l) => ($l->quantity ?? 0) * ($l->unit_price ?? 0));
        $discountAmount = $subtotal * ($selectedVente->remise / 100);
        $totalAfterDiscount = $subtotal - $discountAmount;

        $totalPaid = $selectedVente->paiements()->sum('montant');
        $remaining = max(0, $totalAfterDiscount - $totalPaid);

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
            <tr>
                <td colspan="4" class="text-end fw-bold">Sous-total</td>
                <td class="text-end fw-bold">
                    {{ number_format($subtotal,2,',',' ') }} {{ $currency }}
                </td>
            </tr>

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

            <tr>
                <td colspan="4" class="text-end fw-bold">Total net</td>
                <td class="text-end fw-bold">
                    {{ number_format($totalAfterDiscount,2,',',' ') }} {{ $currency }}
                </td>
            </tr>

            <tr>
                <td colspan="4" class="text-end fw-bold text-success">
                    <i class="fas fa-money-bill-wave me-1"></i> Montant payé
                </td>
                <td class="text-end fw-bold text-success">
                    {{ number_format($totalPaid,2,',',' ') }} {{ $currency }}
                </td>
            </tr>

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

    <div class="{{ $showHeader ? '' : 'd-none d-print-block' }} mt-4 pt-3 border-top text-center small text-muted">
        <p class="mb-0">Merci pour votre fidélité !</p>
    </div>
</div>
