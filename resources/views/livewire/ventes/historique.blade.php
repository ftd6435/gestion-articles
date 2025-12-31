<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="fas fa-chart-line me-2"></i>Historique & Analytics des Ventes
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-filter me-1"></i>
                @if($search || $status || $date_from || $date_to || $client_id)
                    Ventes filtrées
                @else
                    Vue d'ensemble des ventes
                @endif
            </p>
        </div>

        <div class="btn-group" role="group">
            @if($search || $status || $date_from || $date_to || $client_id)
                <button class="btn btn-outline-danger" wire:click="resetFilters" title="Réinitialiser les filtres">
                    <i class="fas fa-undo me-2"></i>Réinitialiser
                </button>
            @endif
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">
                        <i class="fas fa-search me-1"></i>Recherche
                    </label>
                    <input type="text"
                           class="form-control"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Référence, client...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">
                        <i class="fas fa-tag me-1"></i>Statut
                    </label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">Tous</option>
                        <option value="PAYEE">Payée</option>
                        <option value="PARTIELLE">Partielle</option>
                        <option value="IMPAYEE">Impayée</option>
                        <option value="ANNULEE">Annulée</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">
                        <i class="fas fa-calendar me-1"></i>Date début
                    </label>
                    <input type="date"
                           class="form-control"
                           wire:model.live="date_from"
                           max="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">
                        <i class="fas fa-calendar me-1"></i>Date fin
                    </label>
                    <input type="date"
                           class="form-control"
                           wire:model.live="date_to"
                           max="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">
                        <i class="fas fa-user me-1"></i>Client
                    </label>
                    <select class="form-select" wire:model.live="client_id">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    @if($search || $status || $date_from || $date_to || $client_id)
                        <button class="btn btn-outline-secondary w-100"
                                wire:click="resetFilters"
                                title="Supprimer tous les filtres">
                            <i class="fas fa-times me-1"></i>Effacer
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Ventes</h6>
                            <h4 class="mb-0 fw-bold">{{ $totalVentes }}</h4>
                        </div>
                        <div class="bg-primary text-white rounded-circle p-2">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Net</h6>
                            <h4 class="mb-0 fw-bold text-info">
                                {{ number_format($totalNet, 0, ',', ' ') }} FG
                            </h4>
                        </div>
                        <div class="bg-info text-white rounded-circle p-2">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4">
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
                                    ({{ number_format(($totalRemise / $totalNet) * 100, 1) }}%)
                                </small>
                            @endif
                        </div>
                        <div class="bg-warning text-white rounded-circle p-2">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-4">
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
                                    ({{ number_format(($totalPaid / $totalFinal) * 100, 1) }}%)
                                </small>
                            @endif
                        </div>
                        <div class="bg-success text-white rounded-circle p-2">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Dû</h6>
                            <h4 class="mb-0 fw-bold text-danger">
                                {{ number_format($totalDue, 0, ',', ' ') }} FG
                            </h4>
                            @if($totalFinal > 0)
                                <small class="text-muted">
                                    ({{ number_format(($totalDue / $totalFinal) * 100, 1) }}%)
                                </small>
                            @endif
                        </div>
                        <div class="bg-danger text-white rounded-circle p-2">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm bg-secondary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">En cours</h6>
                            <h4 class="mb-0 fw-bold text-secondary">{{ $ventesInProgress }}</h4>
                            @if($totalVentes > 0)
                                <small class="text-muted">
                                    ({{ number_format(($ventesInProgress / $totalVentes) * 100, 1) }}%)
                                </small>
                            @endif
                        </div>
                        <div class="bg-secondary text-white rounded-circle p-2">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-3 mb-4">
        <!-- Chart Type Selector -->
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-chart-bar me-1"></i>Type de graphique
                            </label>
                            <select class="form-select" wire:model.live="chartType">
                                <option value="monthly">Ventes mensuelles</option>
                                <option value="daily">Ventes quotidiennes</option>
                                <option value="status">Répartition par statut</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="button"
                                        class="btn {{ $chartPeriod === 'last_week' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="$set('chartPeriod', 'last_week')">
                                    Cette semaine
                                </button>
                                <button type="button"
                                        class="btn {{ $chartPeriod === 'last_month' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="$set('chartPeriod', 'last_month')">
                                    Ce mois
                                </button>
                                <button type="button"
                                        class="btn {{ $chartPeriod === 'current_year' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="$set('chartPeriod', 'current_year')">
                                    Cette année
                                </button>
                                <button type="button"
                                        class="btn {{ $chartPeriod === 'last_year' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="$set('chartPeriod', 'last_year')">
                                    L'année dernière
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chart Area -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-chart-line me-2"></i>
                        @if($chartType === 'monthly')
                            Évolution des ventes mensuelles
                        @elseif($chartType === 'daily')
                            Évolution des ventes quotidiennes
                        @else
                            Répartition des ventes par statut
                        @endif
                    </h5>

                    <div style="height: 300px;">
                        <canvas id="chartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Sidebar -->
        <div class="col-xl-4">
            <div class="row g-3">
                <!-- Top Clients -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-crown me-2"></i>Top 5 Clients
                            </h5>

                            @if($topClients->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($topClients as $index => $client)
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                    <div>
                                                        <h6 class="mb-0">{{ $client['name'] }}</h6>
                                                        <small class="text-muted">{{ $client['ventes_count'] }} ventes</small>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold">{{ number_format($client['total_amount'], 0, ',', ' ') }} FG</div>
                                                    <small class="text-muted">
                                                        @if($totalNet > 0)
                                                            {{ number_format(($client['total_amount'] / $totalNet) * 100, 1) }}%
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p class="mb-0">Aucun client trouvé</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-chart-pie me-2"></i>Répartition par statut
                            </h5>

                            @if($statusDistribution->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($statusDistribution as $stat)
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge me-2" style="background-color: {{ $stat['color'] }}">&nbsp;</span>
                                                    <div>
                                                        <h6 class="mb-0">{{ $stat['label'] }}</h6>
                                                        <small class="text-muted">{{ $stat['count'] }} ventes</small>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold">{{ number_format($stat['total'], 0, ',', ' ') }} FG</div>
                                                    <small class="text-muted">
                                                        @if($totalVentes > 0)
                                                            {{ number_format(($stat['count'] / $totalVentes) * 100, 1) }}%
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-chart-pie fa-2x mb-2"></i>
                                    <p class="mb-0">Aucune donnée de statut</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Liste des Ventes
                </h5>
                <div class="text-muted small">
                    Affichage {{ $ventes->firstItem() ?? 0 }} à {{ $ventes->lastItem() ?? 0 }} sur {{ $ventes->total() }} ventes
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="bg-primary text-white">#</th>
                        <th class="bg-primary text-white">Référence</th>
                        <th class="bg-primary text-white">Client</th>
                        <th class="bg-primary text-white">Date</th>
                        <th class="text-end bg-primary text-white">Net</th>
                        <th class="text-end bg-primary text-white">Remise</th>
                        <th class="text-end bg-primary text-white">Final</th>
                        <th class="text-end bg-primary text-white">Payé</th>
                        <th class="text-end bg-primary text-white">Reste</th>
                        <th class="bg-primary text-white">Statut</th>
                        <th class="text-end bg-primary text-white">Actions</th>
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
                            <td>{{ ($ventes->currentPage() - 1) * $ventes->perPage() + $index + 1 }}</td>
                            <td class="fw-semibold">{{ $vente->reference }}</td>
                            <td>
                                <div>{{ $vente->client?->name ?? '—' }}</div>
                                @if($vente->client?->telephone)
                                    <small class="text-muted">{{ $vente->client->telephone }}</small>
                                @endif
                            </td>
                            <td class="small">{{ \Carbon\Carbon::parse($vente->date_facture)->format('d/m/Y') }}</td>
                            <td class="text-end small">
                                {{ number_format($netAmount, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end small">
                                @if($vente->remise > 0)
                                    <span class="text-warning">
                                        {{ number_format($discountAmount, 0, ',', ' ') }} {{ $currency }}
                                    </span> |
                                    <small class="text-muted">({{ $vente->remise }}%)</small>
                                @else
                                    <span class="text-muted">0 {{ $currency }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold small">
                                {{ number_format($finalAmount, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end text-success small">
                                {{ number_format($paid, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td class="text-end fw-semibold small {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($remaining, 0, ',', ' ') }} {{ $currency }}
                            </td>
                            <td>
                                @php
                                    $config = match($vente->status) {
                                        'PAYEE' => [
                                            'class' => 'bg-success bg-opacity-10 text-success border-success',
                                            'icon' => 'fa-check-circle',
                                            'label' => 'Payée'
                                        ],
                                        'PARTIELLE' => [
                                            'class' => 'bg-warning bg-opacity-10 text-warning border-warning',
                                            'icon' => 'fa-exclamation-circle',
                                            'label' => 'Partielle'
                                        ],
                                        'IMPAYEE' => [
                                            'class' => 'bg-danger bg-opacity-10 text-danger border-danger',
                                            'icon' => 'fa-times-circle',
                                            'label' => 'Impayée'
                                        ],
                                        'ANNULEE' => [
                                            'class' => 'bg-secondary bg-opacity-10 text-secondary border-secondary',
                                            'icon' => 'fa-ban',
                                            'label' => 'Annulée'
                                        ],
                                        default => [
                                            'class' => 'bg-info bg-opacity-10 text-info border-info',
                                            'icon' => 'fa-info-circle',
                                            'label' => ucfirst($vente->status)
                                        ],
                                    };
                                @endphp

                                <span class="badge {{ $config['class'] }} border border-opacity-25">
                                    <i class="fas {{ $config['icon'] }} me-1"></i> {{ $config['label'] }}
                                </span>
                            </td>
                            {{-- In the table row --}}
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary"
                                            wire:click="showDetails({{ $vente->id }})"
                                            title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">Aucune vente trouvée</p>
                                @if($search || $status || $date_from || $date_to || $client_id)
                                    <p class="small mt-2">
                                        <button class="btn btn-sm btn-link" wire:click="resetFilters">
                                            Réinitialiser les filtres
                                        </button>
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @endforelse

                    {{-- Summary Row --}}
                    @if($ventes->count() > 0)
                        @php
                            $totalFinal = $totalNet - $totalRemise;
                        @endphp
                        <tr class="table-active">
                            <td colspan="4" class="text-end fw-bold">TOTAUX:</td>
                            <td class="text-end fw-bold small">
                                {{ number_format($totalNet, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end text-warning fw-bold small">
                                {{ number_format($totalRemise, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end fw-bold small">
                                {{ number_format($totalFinal, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end text-success fw-bold small">
                                {{ number_format($totalPaid, 0, ',', ' ') }} FG
                            </td>
                            <td class="text-end text-danger fw-bold small">
                                {{ number_format($totalDue, 0, ',', ' ') }} FG
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($ventes->hasPages())
            <div class="card-footer border-0 bg-light">
                {{ $ventes->links() }}
            </div>
        @endif
    </div>

    {{-- Details Modal --}}
    @if($showDetailsModal && $selectedVente)
        @include('livewire.ventes.vente-details-modal')
    @endif
</div>


@script
<script>
    // Store initial data
    window.chartData = @js($chartData);
    window.chartType = @js($chartType);

    // console.log('Initial chart data:', {
    //     type: window.chartType,
    //     dataLength: window.chartData?.length || 0,
    //     data: window.chartData
    // });

    // Simple debounce function
    let chartUpdateTimeout = null;
    function debounceChartUpdate() {
        if (chartUpdateTimeout) {
            clearTimeout(chartUpdateTimeout);
        }
        chartUpdateTimeout = setTimeout(() => {
            if (window.updateChart) {
                // console.log('Debounced chart update triggered');
                window.updateChart();
            }
        }, 300);
    }

    // Listen for Livewire events - handle array structure
    $wire.on('chart-updated', (eventData) => {
        // console.log('Livewire event received:', eventData);

        // Handle the event data structure
        // The data might be in an array or direct object
        let chartData, chartType;

        if (Array.isArray(eventData) && eventData.length > 0) {
            // Data is in array format: [{chartData: [...], chartType: '...'}]
            chartData = eventData[0].chartData || eventData[0];
            chartType = eventData[0].chartType || window.chartType;
        } else if (eventData && typeof eventData === 'object') {
            // Data is direct object: {chartData: [...], chartType: '...'}
            chartData = eventData.chartData || eventData;
            chartType = eventData.chartType || window.chartType;
        } else {
            console.error('Invalid chart data structure:', eventData);
            return;
        }

        // console.log('Extracted chart data:', {
        //     type: chartType,
        //     dataLength: chartData?.length || 0
        // });

        // Update window data
        window.chartData = chartData;
        window.chartType = chartType;

        // Debounce the chart update
        debounceChartUpdate();
    });

    // Initial chart update
    setTimeout(debounceChartUpdate, 500);
</script>
@endscript
