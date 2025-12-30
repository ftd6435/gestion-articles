<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Gestion des Articles</h1>
            <p class="text-muted mb-0">Gérez et analysez vos articles en détail</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="create" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i> Nouvel article
            </button>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total articles</p>
                            <h4 class="fw-bold mb-0">{{ $articles->total() }}</h4>
                        </div>
                        <div class="rounded bg-primary bg-opacity-10 p-2">
                            <i class="fas fa-boxes text-primary"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success">{{ $activeCount ?? 0 }} actifs</span>
                        <span class="badge bg-secondary">{{ $inactiveCount ?? 0 }} inactifs</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Moyenne prix achat</p>
                            <h4 class="fw-bold mb-0">{{ number_format($avgPurchasePrice, 2, ',', ' ') }} FG</h4>
                        </div>
                        <div class="rounded bg-info bg-opacity-10 p-2">
                            <i class="fas fa-shopping-cart text-info"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-muted">Min: {{ number_format($minPurchasePrice, 2, ',', ' ') }}</span>
                        <span class="text-muted ms-2">Max: {{ number_format($maxPurchasePrice, 2, ',', ' ') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Moyenne prix vente</p>
                            <h4 class="fw-bold mb-0">{{ number_format($avgSalePrice, 2, ',', ' ') }} FG</h4>
                        </div>
                        <div class="rounded bg-success bg-opacity-10 p-2">
                            <i class="fas fa-tags text-success"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-muted">Marge moyenne: {{ number_format($avgMargin, 1, ',', ' ') }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Ventes 30 jours</p>
                            <h4 class="fw-bold mb-0">{{ $recentSalesCount ?? 0 }}</h4>
                        </div>
                        <div class="rounded bg-warning bg-opacity-10 p-2">
                            <i class="fas fa-chart-line text-warning"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-muted">Top catégorie: {{ $topCategory ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <!-- Search -->
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label fw-semibold">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            class="form-control"
                            placeholder="Référence, désignation...">
                    </div>
                </div>

                <!-- Category -->
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-semibold">Catégorie</label>
                    <select wire:model.live="filterCategory" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Devise -->
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-semibold">Devise</label>
                    <select wire:model.live="filterDevise" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($devises as $devise)
                            <option value="{{ $devise->id }}">
                                {{ $devise->code }} ({{ $devise->symbole }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-semibold">Statut</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">Tous</option>
                        <option value="active">Actifs</option>
                        <option value="inactive">Inactifs</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <button
                        wire:click="resetFilters"
                        @disabled(!$search && !$filterCategory && !$filterDevise && !$filterStatus)
                        class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-rotate-left me-1"></i>
                        Réinitialiser
                    </button>
                    <button wire:click="toggleAdvancedFilters" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-filter me-1"></i>
                        Plus de filtres
                    </button>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                @if($showAdvancedFilters)
                <div class="col-12 mt-3 border-top pt-3">
                    <h6 class="fw-bold mb-3">Filtres avancés</h6>
                    <div class="row g-3">
                        <!-- Stock Level Filter -->
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold">Niveau de stock</label>
                            <select wire:model.live="filterStockLevel" class="form-select">
                                <option value="">Tous</option>
                                <option value="low">Stock faible (< 10)</option>
                                <option value="medium">Stock moyen (10-50)</option>
                                <option value="high">Stock élevé (> 50)</option>
                                <option value="out">Rupture</option>
                            </select>
                        </div>

                        <!-- Margin Filter -->
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold">Marge bénéficiaire</label>
                            <select wire:model.live="filterMargin" class="form-select">
                                <option value="">Toutes</option>
                                <option value="low">Faible (< 20%)</option>
                                <option value="medium">Moyenne (20-50%)</option>
                                <option value="high">Élevée (> 50%)</option>
                            </select>
                        </div>

                        <!-- Last Updated Filter -->
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold">Dernière mise à jour</label>
                            <select wire:model.live="filterLastUpdated" class="form-select">
                                <option value="">Tous</option>
                                <option value="today">Aujourd'hui</option>
                                <option value="week">Cette semaine</option>
                                <option value="month">Ce mois</option>
                                <option value="year">Cette année</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Article Cards -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">Liste des articles</h5>
                <small class="text-muted">Cliquez sur un article pour voir les détails</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary">{{ $articles->total() }} article(s)</span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#" wire:click.prevent="$refresh">
                                <i class="fas fa-sync-alt me-2"></i>Actualiser
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="dropdown-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="showAdvanced" wire:model.live="showAdvancedFilters">
                                    <label class="form-check-label" for="showAdvanced">Filtres avancés</label>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @include('components.shared.alerts')

            <!-- Desktop / Tablet View -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="bg-primary text-white" width="60">#</th>
                            <th class="bg-primary text-white">Article</th>
                            <th class="bg-primary text-white">Catégorie</th>
                            <th class="text-center bg-primary text-white">Stock</th>
                            <th class="text-end bg-primary text-white">Prix</th>
                            <th class="text-center bg-primary text-white">Statistiques</th>
                            <th width="100" class="text-center bg-primary text-white">Status</th>
                            <th width="150" class="text-end bg-primary text-white">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($articles as $article)
                            @php
                                // Calculate article statistics
                                $totalOrdered = $article->ligneCommandes->sum('quantity');
                                $totalReceived = $article->ligneReceptions->sum('quantity');
                                $totalSold = $article->ligneVentes->sum('quantity');
                                $availableStock = $totalReceived - $totalSold;
                                $stockPercentage = $totalReceived > 0 ? ($availableStock / $totalReceived) * 100 : 0;
                                $margin = $article->prix_achat > 0 ? (($article->prix_vente - $article->prix_achat) / $article->prix_achat) * 100 : 0;
                            @endphp
                            <tr wire:click="showArticleDetails({{ $article->id }})" style="cursor: pointer;" class="hover-row">
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $loop->iteration + (($articles->currentPage() - 1) * $articles->perPage()) }}</span>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="article-avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-semibold">{{ $article->reference }}</h6>
                                            <small class="text-muted">{{ Str::limit($article->designation, 40) }}</small>
                                            @if($article->description)
                                                <div class="mt-1">
                                                    <small class="text-muted"><i>{{ Str::limit($article->description, 30) }}</i></small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $article->category->name ?? '—' }}
                                    </span>
                                    <div class="small text-muted mt-1">
                                        {{ $article->unite ?? 'Unité' }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="position-relative" style="width: 60px; height: 60px;">
                                            <svg width="60" height="60" viewBox="0 0 42 42" class="donut">
                                                <circle class="donut-hole" cx="21" cy="21" r="15.91549430918954" fill="transparent"></circle>
                                                <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#e9ecef" stroke-width="6"></circle>
                                                <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                                        stroke="{{ $stockPercentage > 30 ? '#28a745' : ($stockPercentage > 10 ? '#ffc107' : '#dc3545') }}"
                                                        stroke-width="6" stroke-dasharray="{{ $stockPercentage }} {{ 100 - $stockPercentage }}"
                                                        stroke-dashoffset="25" stroke-linecap="round">
                                                </circle>
                                            </svg>
                                            <div class="position-absolute top-50 start-50 translate-middle">
                                                <strong class="fw-bold">{{ $availableStock }}</strong>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-1">Disponible</small>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <div class="mb-1">
                                        <span class="text-muted small">Achat:</span>
                                        <div class="fw-semibold text-info">
                                            {{ number_format($article->prix_achat, 2, ',', ' ') }} {{ $article->devise->symbole ?? $article->devise->code ?? '' }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">Vente:</span>
                                        <div class="fw-semibold text-success">
                                            {{ number_format($article->prix_vente, 2, ',', ' ') }} {{ $article->devise->symbole ?? $article->devise->code ?? '' }}
                                        </div>
                                    </div>
                                    <div class="small mt-1">
                                        <span class="badge {{ $margin >= 30 ? 'bg-success' : ($margin >= 10 ? 'bg-warning' : 'bg-danger') }} bg-opacity-10 text-{{ $margin >= 30 ? 'success' : ($margin >= 10 ? 'warning' : 'danger') }}">
                                            {{ number_format($margin, 1, ',', ' ') }}% marge
                                        </span>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="row g-1">
                                        <div class="col-6">
                                            <div class="bg-light rounded p-2">
                                                <div class="fw-bold text-primary">{{ $totalOrdered }}</div>
                                                <small class="text-muted">Commandé</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-light rounded p-2">
                                                <div class="fw-bold text-info">{{ $totalReceived }}</div>
                                                <small class="text-muted">Reçu</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mt-1">
                                            <div class="bg-light rounded p-2">
                                                <div class="fw-bold text-success">{{ $totalSold }}</div>
                                                <small class="text-muted">Vendu</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mt-1">
                                            <div class="bg-light rounded p-2">
                                                <div class="fw-bold {{ $availableStock > 10 ? 'text-success' : ($availableStock > 0 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $availableStock }}
                                                </div>
                                                <small class="text-muted">Stock</small>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox"
                                               wire:change="toggleStatus({{ $article->id }})"
                                               id="status{{ $article->id }}"
                                               {{ $article->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status{{ $article->id }}"></label>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge {{ $article->status ? 'bg-success' : 'bg-secondary' }} small">
                                            {{ $article->status ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click.stop="edit({{ $article->id }})"
                                                class="btn btn-outline-primary"
                                                data-bs-toggle="tooltip"
                                                title="Modifier">
                                            <i class="fa fa-pen"></i>
                                        </button>

                                        <button wire:click.stop="deleteConfirm({{ $article->id }})"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="tooltip"
                                                title="Supprimer">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="mt-1 small text-muted">
                                        {{ $article->updated_at->format('d/m/Y') }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-2">Aucun article trouvé</p>
                                    <small class="text-muted">Essayez de modifier vos filtres de recherche</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="d-md-none">
                @forelse($articles as $article)
                    @php
                        $totalOrdered = $article->ligneCommandes->sum('quantity');
                        $totalReceived = $article->ligneReceptions->sum('quantity');
                        $totalSold = $article->ligneVentes->sum('quantity');
                        $availableStock = $totalReceived - $totalSold;
                        $margin = $article->prix_achat > 0 ? (($article->prix_vente - $article->prix_achat) / $article->prix_achat) * 100 : 0;
                    @endphp
                    <div class="border-bottom p-3" wire:click="showArticleDetails({{ $article->id }})" style="cursor: pointer;">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-semibold mb-1">{{ $article->designation }}</h6>
                                <p class="text-muted small mb-1">
                                    Réf: <span class="fw-semibold">{{ $article->reference }}</span>
                                    <span class="badge bg-light text-dark ms-2">{{ $article->category->name ?? '—' }}</span>
                                </p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       wire:change.stop="toggleStatus({{ $article->id }})"
                                       id="mobStatus{{ $article->id }}"
                                       {{ $article->status ? 'checked' : '' }}>
                            </div>
                        </div>

                        <!-- Stock & Prices -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <div class="fw-bold {{ $availableStock > 10 ? 'text-success' : ($availableStock > 0 ? 'text-warning' : 'text-danger') }}">
                                        {{ $availableStock }} dispo
                                    </div>
                                    <small class="text-muted">Stock</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <div class="fw-bold text-success">
                                        {{ number_format($article->prix_vente, 0, ',', ' ') }}
                                    </div>
                                    <small class="text-muted">Prix vente</small>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="row g-2 mb-3">
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold text-primary small">{{ $totalOrdered }}</div>
                                    <small class="text-muted">Cmd</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold text-info small">{{ $totalReceived }}</div>
                                    <small class="text-muted">Reçu</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold text-success small">{{ $totalSold }}</div>
                                    <small class="text-muted">Vendu</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small {{ $margin >= 30 ? 'text-success' : ($margin >= 10 ? 'text-warning' : 'text-danger') }}">
                                        {{ number_format($margin, 0) }}%
                                    </div>
                                    <small class="text-muted">Marge</small>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2 mt-3">
                            <button wire:click.stop="edit({{ $article->id }})"
                                    class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fa fa-pen me-1"></i> Modifier
                            </button>

                            <button wire:click.stop="deleteConfirm({{ $article->id }})"
                                    class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Aucun article trouvé</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($articles->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Affichage de {{ $articles->firstItem() }} à {{ $articles->lastItem() }} sur {{ $articles->total() }} articles
                    </div>
                    <div>
                        {{ $articles->links('livewire::bootstrap') }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Empty State for No Results -->
    @if($articles->count() === 0 && ($search || $filterCategory || $filterDevise || $filterStatus))
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-search fa-4x text-muted opacity-25"></i>
        </div>
        <h5 class="text-muted mb-2">Aucun résultat trouvé</h5>
        <p class="text-muted mb-4">Essayez de modifier vos critères de recherche ou réinitialisez les filtres</p>
        <button wire:click="resetFilters" class="btn btn-primary">
            <i class="fas fa-rotate-left me-2"></i> Réinitialiser les filtres
        </button>
    </div>
    @endif

    <!-- Modal -->
    @include('livewire.articles.article-modal')

</div>

{{-- @push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('livewire:init', () => {
        // Reinitialize tooltips after Livewire updates
        Livewire.hook('element.updated', (el) => {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Livewire event listeners
    document.addEventListener('livewire:load', function() {
        // Handle article details click
        Livewire.on('show-article-details', (data) => {
            // Show article details modal
            const modal = new bootstrap.Modal(document.getElementById('articleDetailsModal'));
            modal.show();
        });

        // Handle export
        Livewire.on('export-started', () => {
            if (typeof toastr !== 'undefined') {
                toastr.info('Export en cours...', '', {timeOut: 3000});
            }
        });

        Livewire.on('export-completed', (data) => {
            if (typeof toastr !== 'undefined') {
                toastr.success('Export terminé!', '', {timeOut: 3000});
            }
            // Trigger download
            const link = document.createElement('a');
            link.href = data.url;
            link.download = data.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Handle delete confirmation
        Livewire.on('confirm-delete', (data) => {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.')) {
                Livewire.dispatch('confirmDelete', {id: data.id});
            }
        });
    });
</script>
@endpush --}}
