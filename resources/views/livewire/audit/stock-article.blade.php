<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Audit stock (expiration)</h1>
            <p class="text-muted mb-0">Articles en stock avec date d'expiration proche</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted">Recherche</label>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                        placeholder="Référence, désignation, catégorie...">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Seuil (jours)</label>
                    <select class="form-select" wire:model.live="daysThreshold">
                        <option value="7">7 jours</option>
                        <option value="15">15 jours</option>
                        <option value="30">30 jours</option>
                        <option value="60">60 jours</option>
                        <option value="90">90 jours</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Inclure expirés</label>
                    <select class="form-select" wire:model.live="includeExpired">
                        <option value="0">Non</option>
                        <option value="1">Oui</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button type="button" class="btn btn-outline-secondary" wire:click="resetFilters">
                        <i class="fa fa-rotate-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Article</th>
                            <th>Catégorie</th>
                            <th>Magasin</th>
                            <th>Étagère</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Qté avec expiration</th>
                            <th>Prochaine expiration</th>
                            <th class="text-center">Jours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                            @php
                                $exp = $article->next_expiration
                                    ? \Carbon\Carbon::parse($article->next_expiration)
                                    : null;
                                $daysLeft = $exp ? $today->diffInDays($exp, false) : null;
                                $badge = 'secondary';
                                $label = '—';
                                if ($daysLeft !== null) {
                                    if ($daysLeft < 0) {
                                        $badge = 'danger';
                                        $label = 'Expiré';
                                    } elseif ($daysLeft <= 7) {
                                        $badge = 'warning';
                                        $label = 'Urgent';
                                    } else {
                                        $badge = 'info';
                                        $label = 'À surveiller';
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $article->reference }}</div>
                                    <div class="text-muted small">{{ $article->designation }}</div>
                                </td>
                                <td class="text-muted">{{ $article->category?->name ?? '—' }}</td>
                                <td class="text-muted">{{ $article->magasin ?? '—' }}</td>
                                <td class="text-muted">{{ $article->etagere ?? '—' }}</td>
                                <td class="text-center fw-bold">{{ (int) $article->stock }}</td>
                                <td class="text-center">{{ (int) ($article->qty_with_expiration ?? 0) }}</td>
                                <td>
                                    @if ($exp)
                                        <span
                                            class="badge bg-{{ $badge }} bg-opacity-10 text-{{ $badge }} border border-{{ $badge }} border-opacity-25 me-2">
                                            {{ $label }}
                                        </span>
                                        <span class="fw-semibold">{{ $exp->format('d/m/Y') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($daysLeft !== null)
                                        <span class="fw-semibold">{{ $daysLeft }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa fa-box-open fa-2x mb-2 opacity-50"></i>
                                    <div>Aucun article trouvé</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
</div>
