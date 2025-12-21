<div>
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="display-5 fw-bold mb-1">Dashboard</h1>
        <p class="text-muted">Bienvenue sur votre tableau de bord</p>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        @foreach($stats as $stat)
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $stat['title'] }}</p>
                            <h3 class="fw-bold mb-2">{{ $stat['value'] }}</h3>
                            <p class="text-success small mb-0">
                                <i class="fas fa-arrow-up"></i> {{ $stat['change'] }}
                            </p>
                        </div>
                        <div class="rounded p-3 bg-{{ $stat['color'] }} bg-opacity-10">
                            <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }} fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts & Tables Section -->
    <div class="row g-4">
        <!-- Recent Sales -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Ventes récentes</h5>
                    <a href="/ventes/historique" class="btn btn-sm btn-link text-primary">
                        Voir tout <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSales as $sale)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $sale['id'] }}</td>
                                    <td>{{ $sale['client'] }}</td>
                                    <td class="fw-bold">{{ $sale['montant'] }}</td>
                                    <td>
                                        @if($sale['statut'] === 'Complété')
                                            <span class="badge bg-success">{{ $sale['statut'] }}</span>
                                        @elseif($sale['statut'] === 'En cours')
                                            <span class="badge bg-info">{{ $sale['statut'] }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ $sale['statut'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $sale['date'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-lg-4">
            <!-- Top Products -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Produits populaires</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded bg-primary bg-opacity-10 p-2 me-3">
                                <i class="fas fa-laptop text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Ordinateur Pro</div>
                                <small class="text-muted">245 ventes</small>
                            </div>
                        </div>
                        <span class="fw-bold">1,234 €</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded bg-success bg-opacity-10 p-2 me-3">
                                <i class="fas fa-mobile-alt text-success"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Smartphone X</div>
                                <small class="text-muted">189 ventes</small>
                            </div>
                        </div>
                        <span class="fw-bold">899 €</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="rounded bg-warning bg-opacity-10 p-2 me-3">
                                <i class="fas fa-headphones text-warning"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Casque Audio</div>
                                <small class="text-muted">167 ventes</small>
                            </div>
                        </div>
                        <span class="fw-bold">199 €</span>
                    </div>
                </div>
            </div>

            <!-- Activity -->
            <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Activité du jour</h5>
                    <div class="mb-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>45 nouvelles commandes</span>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-user-plus me-2"></i>
                        <span>12 nouveaux clients</span>
                    </div>
                    <div>
                        <i class="fas fa-star me-2"></i>
                        <span>23 nouveaux avis</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
