<div>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="display-5 fw-bold mb-1">Ventes du jour</h1>
            <p class="text-muted mb-0">Suivi en temps réel des ventes d'aujourd'hui</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt me-2"></i> Actualiser
            </button>
            <button wire:click="exportExcel" class="btn btn-success">
                <i class="fas fa-file-excel me-2"></i> Exporter
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="mb-0 opacity-75">Chiffre d'affaires</p>
                        <i class="fas fa-euro-sign fs-3 opacity-50"></i>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $statistiques['total'] }}</h2>
                    <p class="mb-0 small">
                        <i class="fas fa-arrow-up me-1"></i> {{ $statistiques['evolution'] }} vs hier
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted mb-0">Nombre de ventes</p>
                        <div class="rounded bg-success bg-opacity-10 p-2">
                            <i class="fas fa-shopping-bag text-success"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $statistiques['nombre_ventes'] }}</h2>
                    <p class="text-muted mb-0 small">Commandes traitées</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted mb-0">Panier moyen</p>
                        <div class="rounded bg-info bg-opacity-10 p-2">
                            <i class="fas fa-chart-line text-info"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $statistiques['panier_moyen'] }}</h2>
                    <p class="text-muted mb-0 small">Par transaction</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted mb-0">Taux de conversion</p>
                        <div class="rounded bg-warning bg-opacity-10 p-2">
                            <i class="fas fa-percentage text-warning"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">68%</h2>
                    <p class="text-success mb-0 small">
                        <i class="fas fa-arrow-up me-1"></i> +5% vs hier
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <label class="mb-0 fw-semibold">Période :</label>
                        <select wire:model.live="selectedPeriode" class="form-select" style="width: auto;">
                            <option value="aujourdhui">Aujourd'hui</option>
                            <option value="hier">Hier</option>
                            <option value="semaine">Cette semaine</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-flex align-items-center gap-2">
                        <span class="text-muted small">Mise à jour :</span>
                        <span class="badge bg-primary">il y a 2 min</span>
                        <span class="spinner-grow spinner-grow-sm text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventes Table -->
    <div class="card">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold">Liste des ventes</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID Vente</th>
                            <th>Heure</th>
                            <th>Client</th>
                            <th>Produits</th>
                            <th>Qté</th>
                            <th>Montant</th>
                            <th>Paiement</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventes as $vente)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $vente['id'] }}</td>
                            <td>{{ $vente['heure'] }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($vente['client']) }}&background=random"
                                         class="rounded-circle me-2"
                                         width="32"
                                         height="32"
                                         alt="Avatar">
                                    <span>{{ $vente['client'] }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $vente['produits'] }}</td>
                            <td>{{ $vente['quantite'] }}</td>
                            <td class="fw-bold">{{ number_format($vente['montant'], 2) }} €</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($vente['paiement'] === 'Carte bancaire')
                                        <i class="fas fa-credit-card text-primary"></i>
                                    @elseif($vente['paiement'] === 'PayPal')
                                        <i class="fab fa-paypal text-info"></i>
                                    @else
                                        <i class="fas fa-university text-success"></i>
                                    @endif
                                    <span class="small">{{ $vente['paiement'] }}</span>
                                </div>
                            </td>
                            <td>
                                @if($vente['statut'] === 'Validé')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Validé
                                    </span>
                                @elseif($vente['statut'] === 'En cours')
                                    <span class="badge bg-info">
                                        <i class="fas fa-clock me-1"></i> En cours
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i> Annulé
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" title="Imprimer">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <p class="mb-0 text-muted">
                    Affichage de <span class="fw-semibold">{{ count($ventes) }}</span> ventes
                </p>
                <p class="mb-0 fw-bold">
                    Total : {{ number_format(array_sum(array_column($ventes, 'montant')), 2) }} €
                </p>
            </div>
        </div>
    </div>
</div>
