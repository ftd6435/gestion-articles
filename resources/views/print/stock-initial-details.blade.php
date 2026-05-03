@php
    $showHeader = $showHeader ?? false;
    $company = \App\Models\CompanySetting::query()->first();
    $companyName = $company?->name ?: config('app.name');
    $companyTel = $company?->telephone;
    $companyEmail = $company?->email;
    $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;

    $filters = $filters ?? [];
    $publicUrl = \Illuminate\Support\Facades\URL::signedRoute('public.stock-initial.show', $filters);
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' . urlencode($publicUrl);
@endphp

<div class="p-4" id="printableArea">
    <div class="{{ $showHeader ? '' : 'd-none d-print-block' }} mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3">
                @if ($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="Logo" style="max-height: 60px; max-width: 160px;">
                @endif
                <div>
                    <div class="fw-bold fs-5">{{ $companyName }}</div>
                    <div class="small text-muted">
                        @if ($companyTel)
                            <span>{{ $companyTel }}</span>
                        @endif
                        @if ($companyTel && $companyEmail)
                            <span class="mx-2">|</span>
                        @endif
                        @if ($companyEmail)
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
            <h2 class="mb-1">STOCK INITIAL DES ARTICLES</h2>
            <div class="mb-0">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
        <hr class="mt-3 mb-0">
    </div>

    @if (isset($filters) && (isset($filters['magasin']) || isset($filters['etagere']) || isset($filters['search'])))
        <div class="mb-3 text-muted small">
            <strong>Filtres appliqués:</strong>
            @if (isset($filters['magasin']))
                <span class="badge bg-secondary ms-2">Magasin: {{ $filters['magasin'] }}</span>
            @endif
            @if (isset($filters['etagere']))
                <span class="badge bg-secondary ms-2">Étagère: {{ $filters['etagere'] }}</span>
            @endif
            @if (isset($filters['search']))
                <span class="badge bg-secondary ms-2">Recherche: {{ $filters['search'] }}</span>
            @endif
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Article</th>
                    <th>Magasin</th>
                    <th>Étagère</th>
                    <th class="text-center">Quantité</th>
                    <th>Date inventaire</th>
                    <th>Expiration</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $index => $stock)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $stock->article->reference }}</div>
                            <div class="small text-muted">{{ $stock->article->designation }}</div>
                        </td>
                        <td>{{ $stock->magasin->nom }}</td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                {{ $stock->etagere->code_etagere }}
                            </span>
                        </td>
                        <td class="text-center fw-bold">{{ number_format($stock->quantity, 0) }}</td>
                        <td>{{ $stock->date_inventaire->format('d/m/Y') }}</td>
                        <td>
                            @if ($stock->date_expiration)
                                @if ($stock->isExpired())
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $stock->date_expiration->format('d/m/Y') }}
                                    </span>
                                @elseif($stock->isExpiringSoon(30))
                                    <span
                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $stock->date_expiration->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                        {{ $stock->date_expiration->format('d/m/Y') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ \Illuminate\Support\Str::limit($stock->notes, 50) ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Aucun stock initial trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($stocks->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Total des quantités:</td>
                        <td class="text-center fw-bold">{{ number_format($stocks->sum('quantity'), 0) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if ($showHeader)
        <div class="mt-4 text-center text-muted small">
            <p class="mb-0">Document généré par {{ config('app.name') }} - {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    @endif
</div>
