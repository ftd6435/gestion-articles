<div>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Anciens - Rapports</h1>
            <p class="text-muted mb-0">Statistiques des anciennes dettes et paiements</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Dettes clients ouvertes</div>
                    <div class="fw-bold fs-4">{{ $openClientCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Dettes fournisseurs ouvertes</div>
                    <div class="fw-bold fs-4">{{ $openFournisseurCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Paiements clients (période)</div>
                    <div class="fw-bold fs-4">{{ $clientPaymentCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Paiements fournisseurs (période)</div>
                    <div class="fw-bold fs-4">{{ $fournisseurPaymentCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Date début</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date fin</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">
                        Les totaux de paiements sont filtrés uniquement si les deux dates sont définies.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <div class="fw-semibold">Dettes clients ouvertes (reste à payer)</div>
                </div>
                <div class="card-body">
                    @if ($openClientTotals->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Devise</th>
                                        <th class="text-end">Total restant</th>
                                        <th class="text-end">Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($openClientTotals as $row)
                                        @php
                                            $devise = $row['devise'];
                                            $currency = $devise?->symbole ?? ($devise?->code ?? '—');
                                        @endphp
                                        <tr>
                                            <td>{{ $currency }}</td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format((float) $row['total_remaining'], 2, ',', ' ') }}</td>
                                            <td class="text-end text-muted">{{ (int) $row['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">Aucune dette ouverte.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <div class="fw-semibold">Dettes fournisseurs ouvertes (reste à payer)</div>
                </div>
                <div class="card-body">
                    @if ($openFournisseurTotals->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Devise</th>
                                        <th class="text-end">Total restant</th>
                                        <th class="text-end">Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($openFournisseurTotals as $row)
                                        @php
                                            $devise = $row['devise'];
                                            $currency = $devise?->symbole ?? ($devise?->code ?? '—');
                                        @endphp
                                        <tr>
                                            <td>{{ $currency }}</td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format((float) $row['total_remaining'], 2, ',', ' ') }}</td>
                                            <td class="text-end text-muted">{{ (int) $row['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">Aucune dette ouverte.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <div class="fw-semibold">Paiements clients (période)</div>
                </div>
                <div class="card-body">
                    @if ($clientPaidTotals->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Devise</th>
                                        <th class="text-end">Total payé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clientPaidTotals as $row)
                                        @php
                                            $devise = $row['devise'];
                                            $currency = $devise?->symbole ?? ($devise?->code ?? '—');
                                        @endphp
                                        <tr>
                                            <td>{{ $currency }}</td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format((float) $row['total_paid'], 2, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">Aucun paiement dans la période.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <div class="fw-semibold">Paiements fournisseurs (période)</div>
                </div>
                <div class="card-body">
                    @if ($fournisseurPaidTotals->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Devise</th>
                                        <th class="text-end">Total payé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fournisseurPaidTotals as $row)
                                        @php
                                            $devise = $row['devise'];
                                            $currency = $devise?->symbole ?? ($devise?->code ?? '—');
                                        @endphp
                                        <tr>
                                            <td>{{ $currency }}</td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format((float) $row['total_paid'], 2, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">Aucun paiement dans la période.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
