<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport des ventes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    @php
        $company = \App\Models\CompanySetting::query()->first();
        $companyName = $company?->name ?: config('app.name');
        $companyTel = $company?->telephone;
        $companyEmail = $company?->email;
        $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;
    @endphp

    <div class="container py-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
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
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                </div>

                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1">Rapport des Ventes</h2>
                    <div class="text-muted">
                        {{ $periodeLabels[$selectedPeriode] ?? $selectedPeriode }}
                        ({{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                        @if ($dateFrom != $dateTo)
                            au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                        @endif
                        )
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 bg-primary bg-opacity-10 h-100">
                            <div class="card-body text-center">
                                <div class="h4 fw-bold text-primary mb-1">{{ $totalVentes }}</div>
                                <div class="small text-muted">Ventes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 bg-success bg-opacity-10 h-100">
                            <div class="card-body text-center">
                                <div class="h4 fw-bold text-success mb-1">{{ number_format($totalMontant, 0, ',', ' ') }}</div>
                                <div class="small text-muted">Total ({{ $currency }})</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 bg-info bg-opacity-10 h-100">
                            <div class="card-body text-center">
                                <div class="h4 fw-bold text-info mb-1">{{ $totalArticles }}</div>
                                <div class="small text-muted">Articles</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 bg-success bg-opacity-10 h-100">
                            <div class="card-body text-center">
                                <div class="h4 fw-bold text-success mb-1">{{ number_format($totalPayee, 0, ',', ' ') }}</div>
                                <div class="small text-muted">Payé ({{ $currency }})</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 bg-warning bg-opacity-10 h-100">
                            <div class="card-body text-center">
                                <div class="h4 fw-bold text-warning mb-1">{{ number_format($totalReste, 0, ',', ' ') }}</div>
                                <div class="small text-muted">Reste ({{ $currency }})</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Payé</th>
                                <th class="text-end">Reste</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventes as $vente)
                                @php
                                    $total = $vente->totalAfterRemise();
                                    $paid = $vente->totalPaid();
                                    $reste = max(0, $total - $paid);
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $vente->reference }}</td>
                                    <td>{{ \Carbon\Carbon::parse($vente->date_facture)->format('d/m/Y') }}</td>
                                    <td>{{ $vente->client?->name ?? '—' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($total, 0, ',', ' ') }} {{ $currency }}</td>
                                    <td class="text-end text-success fw-bold">{{ number_format($paid, 0, ',', ' ') }} {{ $currency }}</td>
                                    <td class="text-end text-warning fw-bold">{{ number_format($reste, 0, ',', ' ') }} {{ $currency }}</td>
                                    <td>
                                        @php
                                            $statusMap = [
                                                'PAYEE' => ['success', 'Payée'],
                                                'PARTIELLE' => ['warning', 'Partielle'],
                                                'IMPAYEE' => ['danger', 'Impayée'],
                                                'ANNULEE' => ['secondary', 'Annulée'],
                                            ];
                                            [$cls, $lbl] = $statusMap[$vente->status] ?? ['secondary', $vente->status];
                                        @endphp
                                        <span class="badge bg-{{ $cls }} bg-opacity-10 text-{{ $cls }} border border-{{ $cls }} border-opacity-25">
                                            {{ $lbl }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Aucune vente</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
