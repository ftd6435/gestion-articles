<div class="container-fluid py-4" id="ventes-jour-report">
    {{-- Header with Print Controls --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8">
            <h4 class="fw-bold mb-0">
                Rapport de ventes
            </h4>
            <p class="text-muted mb-0" id="periodLabels" data-selectedPeriode="{{ $selectedPeriode }}">
                @php
                    $periodeLabels = [
                        'aujourdhui' => "Aujourd'hui",
                        'hier' => 'Hier',
                        'semaine' => 'Cette semaine',
                        'mois' => 'Ce mois',
                    ];
                @endphp
                <i class="fas fa-calendar me-1"></i>
                {{ $periodeLabels[$selectedPeriode] ?? $selectedPeriode }}
                ({{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                @if($dateFrom != $dateTo)
                    au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                @endif
                )
            </p>
        </div>

        <div class="btn-group col-12 col-md-4" role="group">
            <button type="button" class="btn btn-outline-info dropdown-toggle me-2" data-bs-toggle="dropdown">
                <i class="fas fa-calendar-alt me-2"></i>
                {{ $periodeLabels[$selectedPeriode] ?? 'Période' }}
            </button>
            <ul class="dropdown-menu">
                <li>
                    <button class="dropdown-item" wire:click="changePeriode('aujourdhui')">
                        <i class="fas fa-sun me-2"></i>Aujourd'hui
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" wire:click="changePeriode('hier')">
                        <i class="fas fa-arrow-left me-2"></i>Hier
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" wire:click="changePeriode('semaine')">
                        <i class="fas fa-calendar-week me-2"></i>Cette semaine
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" wire:click="changePeriode('mois')">
                        <i class="fas fa-calendar me-2"></i>Ce mois
                    </button>
                </li>
            </ul>

            <button class="btn btn-primary me-2" onclick="printSalesReport()" title="Imprimer" id="print-btn">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Ventes</h6>
                            <h4 class="mb-0 fw-bold">{{ $totalVentes }}</h4>
                        </div>
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Net</h6>
                            <h4 class="mb-0 fw-bold text-info">
                                {{ number_format($totalNet, 0, ',', ' ') }} FG
                            </h4>
                        </div>
                        <div class="bg-info text-white rounded-circle p-3">
                            <i class="fas fa-chart-bar fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Remise</h6>
                            <h4 class="mb-0 fw-bold text-warning">
                                {{ number_format($totalRemise, 0, ',', ' ') }} FG
                            </h4>
                            @if($totalNet > 0)
                                <small class="text-muted">
                                    ({{ number_format(($totalRemise / $totalNet) * 100, 1) }}% en moyenne)
                                </small>
                            @endif
                        </div>
                        <div class="bg-warning text-white rounded-circle p-3">
                            <i class="fas fa-percentage fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Perçu</h6>
                            <h4 class="mb-0 fw-bold text-success">
                                {{ number_format($totalPaid, 0, ',', ' ') }} FG
                            </h4>
                            @php
                                $totalFinal = $totalNet - $totalRemise;
                            @endphp
                            @if($totalFinal > 0)
                                <small class="text-muted">
                                    ({{ number_format(($totalPaid / $totalFinal) * 100, 1) }}% réglé)
                                </small>
                            @endif
                        </div>
                        <div class="bg-success text-white rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Printable Report Section --}}
    <div class="card shadow-sm border-0" id="printable-section">
        {{-- Report Header (Visible only when printing) --}}
        <div class="print-header d-none">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Rapport des Ventes</h2>
                <h4 class="text-muted">
                    {{ $periodeLabels[$selectedPeriode] ?? $selectedPeriode }}
                    ({{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                    @if($dateFrom != $dateTo)
                        au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                    @endif
                    )
                </h4>
                <p class="text-muted">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>

            <div class="row mb-4">
                <div class="col-3 text-center">
                    <p class="mb-1"><strong>Total Ventes</strong></p>
                    <h4>{{ $totalVentes }}</h4>
                </div>
                <div class="col-3 text-center">
                    <p class="mb-1"><strong>Total Net</strong></p>
                    <h4 class="text-info">{{ number_format($totalNet, 0, ',', ' ') }} FG</h4>
                </div>
                <div class="col-3 text-center">
                    <p class="mb-1"><strong>Total Remise</strong></p>
                    <h4 class="text-warning">{{ number_format($totalRemise, 0, ',', ' ') }} FG</h4>
                </div>
                <div class="col-3 text-center">
                    <p class="mb-1"><strong>Total Perçu</strong></p>
                    <h4 class="text-success">{{ number_format($totalPaid, 0, ',', ' ') }} FG</h4>
                </div>
            </div>
            <hr>
        </div>

        {{-- Sales Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="bg-primary text-white">#</th>
                        <th class="bg-primary text-white">Référence</th>
                        <th class="bg-primary text-white">Client</th>
                        <th class="bg-primary text-white">Date</th>
                        <th class="text-end bg-primary text-white">Montant Net</th>
                        <th class="text-end bg-primary text-white">Remise</th>
                        <th class="text-end bg-primary text-white">Montant Final</th>
                        <th class="text-end bg-primary text-white">Payé</th>
                        <th class="text-end bg-primary text-white">Reste</th>
                        <th class="bg-primary text-white">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventes as $index => $vente)
                        @php
                            // Use model methods
                            $netAmount = $vente->subTotal();
                            $discountAmount = $vente->discountAmount();
                            $finalAmount = $vente->totalAfterRemise();
                            $paid = $vente->totalPaid();
                            $remaining = $vente->remainingAmount();
                            $currency = $vente->devise?->symbole ?? $vente->devise?->code ?? 'FG';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-semibold">{{ $vente->reference }}</td>
                            <td>{{ $vente->client?->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($vente->date_facture)->format('d/m/Y') }}</td>
                            <td class="text-end">
                                {{ number_format($netAmount, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end">
                                @if($vente->remise > 0)
                                    <span class="text-warning">
                                        {{ number_format($discountAmount, 0, ',', ' ') }} {{ $currency }}
                                    </span> |
                                    <small class="text-muted">({{ $vente->remise }}%)</small>
                                @else
                                    <span class="text-muted">0 {{ $currency }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                {{ number_format($finalAmount, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end text-success">
                                {{ number_format($paid, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end fw-bold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($remaining, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td>
                                @php
                                    $config = match($vente->status) {
                                        'PAYEE' => [
                                            'class' => 'bg-success bg-opacity-10 text-success border-success',
                                            'label' => 'Payée'
                                        ],
                                        'PARTIELLE' => [
                                            'class' => 'bg-warning bg-opacity-10 text-warning border-warning',
                                            'label' => 'Partielle'
                                        ],
                                        'IMPAYEE' => [
                                            'class' => 'bg-danger bg-opacity-10 text-danger border-danger',
                                            'label' => 'Impayée'
                                        ],
                                        default => [
                                            'class' => 'bg-info bg-opacity-10 text-info border-info',
                                            'label' => ucfirst($vente->status)
                                        ],
                                    };
                                @endphp

                                <span class="badge {{ $config['class'] }} border border-opacity-25">
                                    {{ $config['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">Aucune vente trouvée pour cette période</p>
                            </td>
                        </tr>
                    @endforelse

                    {{-- Summary Row --}}
                    @if($ventes->count() > 0)
                        @php
                            $totalFinal = $totalNet - $totalRemise;
                        @endphp
                        <tr class="table-active fw-bold">
                            <td colspan="4" class="text-end">TOTAUX:</td>
                            <td class="text-end">
                                {{ number_format($totalNet, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end text-warning">
                                {{ number_format($totalRemise, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end fw-bold">
                                {{ number_format($totalFinal, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end text-success">
                                {{ number_format($totalPaid, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end text-danger">
                                {{ number_format($totalDue, 0, ',', ' ') }} FG
                            </td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Print Footer --}}
        @if($ventes->count() > 0)
            <div class="print-footer d-none mt-4 pt-4 border-top">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><strong>Signature du responsable:</strong></p>
                        <div style="height: 50px; border-bottom: 1px solid #000;"></div>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Cachet de l'entreprise:</strong></p>
                        <div style="height: 50px;"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
