<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h2 fw-bold mb-1" style="color: var(--primary);">Tableau de Bord</h1>
            <p class="text-muted mb-0">Vue d'ensemble de votre activité</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="refreshDashboard" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sync-alt me-1"></i> Actualiser
            </button>
            <span class="badge bg-primary bg-opacity-10 text-primary d-flex align-items-center">
                <i class="fas fa-clock me-1"></i> {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <!-- Total Clients -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 hover-lift" style="border-left: 4px solid var(--primary);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Clients</p>
                            <h3 class="fw-bold mb-0">{{ number_format($totalClients) }}</h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success small">
                            <i class="fas fa-plus me-1"></i>{{ $newClientsToday }} aujourd'hui
                        </span>
                        <div class="text-muted small mt-1">{{ $activeClients }} actifs</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Suppliers -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 hover-lift" style="border-left: 4px solid var(--info);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Fournisseurs</p>
                            <h3 class="fw-bold mb-0">{{ number_format($totalSuppliers) }}</h3>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-truck text-info"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-info bg-opacity-10 text-info small">
                            <i class="fas fa-plus me-1"></i>{{ $newSuppliersToday }} aujourd'hui
                        </span>
                        <div class="text-muted small mt-1">{{ $activeSuppliers }} actifs</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Articles -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 hover-lift" style="border-left: 4px solid var(--success);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Articles</p>
                            <h3 class="fw-bold mb-0">{{ number_format($totalArticles) }}</h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-boxes text-success"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success small">
                            <i class="fas fa-plus me-1"></i>{{ $newArticlesToday }} aujourd'hui
                        </span>
                        <div class="text-muted small mt-1">{{ $activeArticles }} actifs</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 hover-lift" style="border-left: 4px solid var(--warning);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Commandes</p>
                            <h3 class="fw-bold mb-0">{{ number_format($totalOrders) }}</h3>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-shopping-cart text-warning"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-warning bg-opacity-10 text-warning small">
                            <i class="fas fa-plus me-1"></i>{{ $newOrdersToday }} aujourd'hui
                        </span>
                        <div class="text-muted small mt-1">{{ $pendingOrders }} en attente</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 hover-lift" style="border-left: 4px solid var(--danger);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Ventes</p>
                            <h3 class="fw-bold mb-0">{{ number_format($totalSales) }}</h3>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-cash-register text-danger"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger small">
                            <i class="fas fa-plus me-1"></i>{{ $newSalesToday }} aujourd'hui
                        </span>
                        <div class="text-muted small mt-1">{{ $pendingSales }} en attente</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100 hover-lift" style="border-left: 4px solid #6c757d;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Chiffre d'affaires</p>
                            <h3 class="fw-bold mb-0">{{ number_format($totalRevenue, 0, ',', ' ') }} FG</h3>
                        </div>
                        <div class="rounded-circle bg-dark bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-money-bill-wave text-dark"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success small">
                            <i class="fas fa-arrow-up me-1"></i>{{ number_format($totalPaymentsReceived, 0, ',', ' ') }} FG reçus
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="row g-4 mb-4">
        <!-- Left Column - Financial Summary -->
        <div class="col-12 col-lg-8">
            <div class="row g-3">
                <!-- Purchases Summary -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-shopping-basket text-warning me-2"></i>Achats & Fournisseurs
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 fw-bold text-warning mb-1">{{ number_format($totalPurchases, 0, ',', ' ') }} FG</div>
                                        <small class="text-muted">Total Achats</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 fw-bold text-info mb-1">{{ number_format($totalPaymentsMade, 0, ',', ' ') }} FG</div>
                                        <small class="text-muted">Paiements Effectués</small>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                        <small class="text-muted">En attente de paiement</small>
                                        <span class="fw-bold text-danger">{{ number_format($pendingPayments, 0, ',', ' ') }} FG</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Summary -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-warehouse text-success me-2"></i>Stock & Inventaire
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 fw-bold text-success mb-1">{{ number_format($totalStockValue, 0, ',', ' ') }} FG</div>
                                        <small class="text-muted">Valeur Stock</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 fw-bold text-primary mb-1">{{ $totalWarehouses }}</div>
                                        <small class="text-muted">Magasins</small>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-danger bg-opacity-10 text-danger me-2">
                                                {{ $outOfStockItems }} rupture
                                            </span>
                                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                                {{ $lowStockItems }} faible stock
                                            </span>
                                        </div>
                                        <small class="text-muted">{{ $totalShelves }} étagères</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories & Currency -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-tags text-primary me-2"></i>Catégories & Devises
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 fw-bold text-primary mb-1">{{ $totalCategories }}</div>
                                        <small class="text-muted">Catégories</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 fw-bold text-info mb-1">{{ $totalCurrency }}</div>
                                        <small class="text-muted">Devises</small>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($topCategories as $category)
                                            <span class="badge bg-light text-dark">
                                                {{ Str::limit($category['name'], 15) }} ({{ $category['article_count'] }})
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accounts Receivable -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-hand-holding-usd text-danger me-2"></i>Créances Clients
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center p-3">
                                <div class="h2 fw-bold text-danger mb-2">{{ number_format($pendingReceivables, 0, ',', ' ') }} FG</div>
                                <small class="text-muted">Montant total en attente</small>
                                <div class="mt-3">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{ number_format($totalPaymentsReceived, 0, ',', ' ') }} FG déjà reçus
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Quick Stats -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>Statistiques Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <!-- Daily Stats -->
                        <div class="list-group-item border-0 px-0 pt-0">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Activité aujourd'hui</small>
                                <span class="badge bg-primary bg-opacity-10 text-primary">Journée</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="text-center">
                                        <div class="fw-bold text-primary">{{ $newClientsToday }}</div>
                                        <small class="text-muted">Clients</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <div class="fw-bold text-success">{{ $newArticlesToday }}</div>
                                        <small class="text-muted">Articles</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <div class="fw-bold text-danger">{{ $newSalesToday }}</div>
                                        <small class="text-muted">Ventes</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Orders Status -->
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Statut Commandes</small>
                                <span class="badge bg-warning bg-opacity-10 text-warning">Suivi</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-warning">{{ $pendingOrders }}</div>
                                        <small class="text-muted">En attente</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-success">{{ $completedOrders }}</div>
                                        <small class="text-muted">Terminées</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sales Status -->
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Statut Ventes</small>
                                <span class="badge bg-success bg-opacity-10 text-success">Ventes</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-warning">{{ $pendingSales }}</div>
                                        <small class="text-muted">En attente</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-success">{{ $completedSales }}</div>
                                        <small class="text-muted">Terminées</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Payments -->
                        <div class="list-group-item border-0 px-0 pb-0">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Paiements aujourd'hui</small>
                                <span class="badge bg-info bg-opacity-10 text-info">{{ $newPaymentsToday }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Total reçu/payé</small>
                                <small class="fw-semibold">{{ number_format($newPaymentsToday * 1000, 0, ',', ' ') }} FG</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left Column - Top Lists -->
        <div class="col-12 col-xl-8">
            <div class="row g-4">
                <!-- Top Clients -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold mb-0">
                                <i class="fas fa-crown text-warning me-2"></i>Top 5 Clients
                            </h6>
                            <a href="{{ route('clients') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                                <small>Voir tous</small>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($topClients as $client)
                                    <a href="{{ route('clients') }}"
                                       class="list-group-item border-0 py-3 px-4 hover-bg-light text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-sm bg-{{ $client['avatar']['color'] }} bg-opacity-10 text-{{ $client['avatar']['color'] }} rounded-circle d-flex align-items-center justify-content-center">
                                                    <span class="fw-bold small">{{ $client['avatar']['initials'] }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-semibold">{{ Str::limit($client['name'], 20) }}</h6>
                                                <small class="text-muted">{{ $client['email'] }}</small>
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark small">{{ $client['phone'] }}</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-success">{{ number_format($client['total_spent'], 0, ',', ' ') }} FG</div>
                                                <small class="text-muted">{{ $client['total_purchases'] }} achats</small>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-users fa-lg mb-2 opacity-25"></i>
                                        <p class="small mb-0">Aucun client avec achats</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Suppliers -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold mb-0">
                                <i class="fas fa-medal text-info me-2"></i>Top 5 Fournisseurs
                            </h6>
                            <a href="{{ route('fournisseurs') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                                <small>Voir tous</small>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($topSuppliers as $supplier)
                                    <a href="{{ route('fournisseurs') }}"
                                       class="list-group-item border-0 py-3 px-4 hover-bg-light text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-sm bg-{{ $supplier['avatar']['color'] }} bg-opacity-10 text-{{ $supplier['avatar']['color'] }} rounded-circle d-flex align-items-center justify-content-center">
                                                    <span class="fw-bold small">{{ $supplier['avatar']['initials'] }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-semibold">{{ Str::limit($supplier['name'], 20) }}</h6>
                                                <small class="text-muted">{{ $supplier['email'] }}</small>
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark small">{{ $supplier['phone'] }}</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-primary">{{ number_format($supplier['total_supplied'], 0, ',', ' ') }} FG</div>
                                                <small class="text-muted">{{ $supplier['total_orders'] }} commandes</small>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-truck fa-lg mb-2 opacity-25"></i>
                                        <p class="small mb-0">Aucun fournisseur avec commandes</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Articles -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold mb-0">
                                <i class="fas fa-star text-success me-2"></i>Top 5 Articles Vendus
                            </h6>
                            <a href="{{ route('articles') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                                <small>Voir tous</small>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($topArticles as $article)
                                    <a href="{{ route('articles') }}"
                                       class="list-group-item border-0 py-3 px-4 hover-bg-light text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-box text-success"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-semibold">{{ Str::limit($article['designation'], 25) }}</h6>
                                                <small class="text-muted">Réf: {{ $article['reference'] }}</small>
                                                <div class="mt-1 d-flex gap-1">
                                                    <span class="badge bg-light text-dark small">{{ $article['category'] }}</span>
                                                    <span class="badge bg-success small">{{ $article['total_sold'] }} vendus</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-success">{{ number_format($article['revenue'], 0, ',', ' ') }} FG</div>
                                                <small class="text-muted">{{ $article['stock'] }} en stock</small>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-box fa-lg mb-2 opacity-25"></i>
                                        <p class="small mb-0">Aucun article vendu</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold mb-0">
                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>Alertes Stock Faible
                            </h6>
                            <span class="badge bg-danger">{{ count($lowStockAlerts) }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($lowStockAlerts as $alert)
                                    <a href="{{ route('articles.show', $alert['id']) }}"
                                       class="list-group-item border-0 py-3 px-4 hover-bg-light text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-exclamation text-danger"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-semibold">{{ Str::limit($alert['designation'], 25) }}</h6>
                                                <small class="text-muted">Réf: {{ $alert['reference'] }}</small>
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark">{{ $alert['category'] }}</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="h5 fw-bold text-danger mb-0">{{ $alert['stock'] }}</div>
                                                <small class="text-muted">{{ $alert['unit'] }} restants</small>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle fa-lg mb-2 opacity-25"></i>
                                        <p class="small mb-0">Aucune alerte de stock</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Latest Activities -->
        <div class="col-12 col-xl-4">
            <!-- Latest Orders & Sales Tabs -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs border-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#orders-tab" type="button">
                                <i class="fas fa-shopping-cart me-1"></i> Commandes
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sales-tab" type="button">
                                <i class="fas fa-cash-register me-1"></i> Ventes
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payments-tab" type="button">
                                <i class="fas fa-money-bill-wave me-1"></i> Paiements
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Orders Tab -->
                        <div class="tab-pane fade show active" id="orders-tab">
                            <div class="list-group list-group-flush">
                                @forelse($latestOrders as $order)
                                    <a href="{{ route('stock.commandes') }}"
                                       class="list-group-item border-0 py-3 px-4 hover-bg-light text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $order['reference'] }}</h6>
                                                <small class="text-muted">{{ $order['supplier'] }}</small>
                                            </div>
                                            <span class="badge {{ $order['status'] === 'completed' ? 'bg-success' : 'bg-warning' }}">
                                                {{ $order['status'] === 'completed' ? 'Terminé' : 'En cours' }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>{{ $order['date'] }}
                                                </small>
                                            </div>
                                            <div class="fw-bold text-primary">
                                                {{ number_format($order['amount'], 0, ',', ' ') }} FG
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-shopping-cart fa-lg mb-2 opacity-25"></i>
                                        <p class="small mb-0">Aucune commande récente</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Sales Tab -->
                        <div class="tab-pane fade" id="sales-tab">
                            <div class="list-group list-group-flush">
                                @forelse($latestSales as $sale)
                                    <a href="{{ route('ventes.ventes') }}"
                                       class="list-group-item border-0 py-3 px-4 hover-bg-light text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $sale['reference'] }}</h6>
                                                <small class="text-muted">{{ $sale['client'] }}</small>
                                            </div>
                                            <span class="badge {{ $sale['payment_status'] === 'payé' ? 'bg-success' : ($sale['payment_status'] === 'partiel' ? 'bg-warning' : 'bg-secondary') }}">
                                                {{ $sale['payment_status'] }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>{{ $sale['date'] }}
                                                </small>
                                            </div>
                                            <div class="fw-bold text-success">
                                                {{ number_format($sale['amount'], 0, ',', ' ') }} FG
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-cash-register fa-lg mb-2 opacity-25"></i>
                                        <p class="small mb-0">Aucune vente récente</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div class="tab-pane fade" id="payments-tab">
                            <div class="list-group list-group-flush">
                                @forelse($latestPayments as $payment)
                                    <div class="list-group-item border-0 py-3 px-4 hover-bg-light">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $payment['reference'] }}</h6>
                                                <small class="text-muted">{{ $payment['from_to'] }}</small>
                                            </div>
                                            <span class="badge {{ $payment['type'] === 'client' ? 'bg-success' : 'bg-info' }}">
                                                {{ $payment['type'] === 'client' ? 'Reçu' : 'Payé' }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>{{ $payment['date'] }}
                                                </small>
                                            </div>
                                            <div class="fw-bold {{ $payment['type'] === 'client' ? 'text-success' : 'text-info' }}">
                                                {{ number_format($payment['amount'], 0, ',', ' ') }} FG
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-money-bill-wave fa-lg mb-2 opacity-25"></i>
                                        <p class="small mb-0">Aucun paiement récent</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="fw-semibold mb-0">
                        <i class="fas fa-bolt text-primary me-2"></i>Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('clients') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus me-2"></i>Client
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('articles') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-box me-2"></i>Articles
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('ventes.create') }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-cash-register me-2"></i>Vente
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('stock.commandes.create') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-shopping-cart me-2"></i>Commande
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
