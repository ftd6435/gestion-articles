<?php

namespace App\Livewire\Ventes;

use App\Models\ClientModel;
use App\Models\Ventes\VenteModel;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Historique extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $date_from = '';
    public $date_to = '';
    public $client_id = '';
    public $chartType = 'monthly';
    public $chartPeriod = 'current_year';

    public $totalVentes = 0;
    public $totalPaid = 0;
    public $totalDue = 0;
    public $totalNet = 0;
    public $totalRemise = 0;
    public $ventesInProgress = 0;

    public $chartData = [];
    public $topClients = [];
    public $statusDistribution = [];

    // Modal properties
    public $showDetailsModal = false;
    public $selectedVente = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'client_id' => ['except' => ''],
        'chartType' => ['except' => 'monthly'],
        'chartPeriod' => ['except' => 'current_year'],
    ];

    public function mount()
    {
        // Set default date range to last 30 days
        $this->date_from = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->date_to = Carbon::now()->format('Y-m-d');

        $this->loadStatistics();
        $this->loadChartData();
        $this->loadAnalytics();
    }

    public function loadStatistics()
    {
        $query = VenteModel::query();
        $this->applyFilters($query);

        // Get counts
        $this->totalVentes = $query->count();

        // Clone query for in-progress count
        $progressQuery = clone $query;
        $this->ventesInProgress = $progressQuery->whereIn('status', ['IMPAYEE', 'PARTIELLE'])->count();

        // Get filtered ventes for calculations
        $ventesQuery = clone $query;
        $ventes = $ventesQuery->with(['ligneVentes', 'paiements', 'client'])->get();

        // Calculate statistics
        $this->totalNet = 0;
        $this->totalRemise = 0;
        $this->totalPaid = 0;
        $this->totalDue = 0;

        foreach ($ventes as $vente) {
            $this->totalNet += $vente->subTotal();
            $this->totalRemise += $vente->discountAmount();
            $this->totalPaid += $vente->totalPaid();
            $this->totalDue += $vente->remainingAmount();
        }
    }

    protected function applyFilters($query)
    {
        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($clientQuery) {
                        $clientQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('adresse', 'like', '%' . $this->search . '%')
                            ->orWhere('telephone', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Status filter
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Date filters
        if ($this->date_from) {
            $query->whereDate('date_facture', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('date_facture', '<=', $this->date_to);
        }

        // Client filter
        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }
    }

    public function loadChartData()
    {
        $query = VenteModel::query();
        $this->applyFilters($query);

        $ventes = $query->with(['ligneVentes', 'paiements'])->get();

        if ($this->chartType === 'monthly') {
            $this->chartData = $this->getMonthlyData($ventes);
        } elseif ($this->chartType === 'daily') {
            $this->chartData = $this->getDailyData($ventes);
        } elseif ($this->chartType === 'status') {
            $this->chartData = $this->getStatusData($ventes);
        }
    }

    protected function getMonthlyData($ventes)
    {
        $data = [];
        $months = [];

        // Get date range
        $startDate = $this->date_from ? Carbon::parse($this->date_from) : Carbon::now()->subYear();
        $endDate = $this->date_to ? Carbon::parse($this->date_to) : Carbon::now();

        // Generate months in range
        $current = $startDate->copy()->startOfMonth();
        while ($current <= $endDate) {
            $months[$current->format('Y-m')] = [
                'label' => $current->format('M Y'),
                'total' => 0,
                'paid' => 0,
                'due' => 0,
                'count' => 0
            ];
            $current->addMonth();
        }

        // Group ventes by month
        foreach ($ventes as $vente) {
            $month = Carbon::parse($vente->date_facture)->format('Y-m');

            if (isset($months[$month])) {
                $months[$month]['total'] += $vente->totalAfterRemise();
                $months[$month]['paid'] += $vente->totalPaid();
                $months[$month]['due'] += $vente->remainingAmount();
                $months[$month]['count']++;
            }
        }

        foreach ($months as $month) {
            $data[] = $month;
        }

        return $data;
    }

    protected function getDailyData($ventes)
    {
        $data = [];
        $days = [];

        $startDate = $this->date_from ? Carbon::parse($this->date_from) : Carbon::now()->subDays(30);
        $endDate = $this->date_to ? Carbon::parse($this->date_to) : Carbon::now();

        // Generate days in range
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $days[$current->format('Y-m-d')] = [
                'label' => $current->format('d/m'),
                'total' => 0,
                'paid' => 0,
                'due' => 0,
                'count' => 0
            ];
            $current->addDay();
        }

        // Group ventes by day
        foreach ($ventes as $vente) {
            $day = Carbon::parse($vente->date_facture)->format('Y-m-d');

            if (isset($days[$day])) {
                $days[$day]['total'] += $vente->totalAfterRemise();
                $days[$day]['paid'] += $vente->totalPaid();
                $days[$day]['due'] += $vente->remainingAmount();
                $days[$day]['count']++;
            }
        }

        foreach ($days as $day) {
            $data[] = $day;
        }

        return $data;
    }

    protected function getStatusData($ventes)
    {
        $statuses = [
            'PAYEE' => ['label' => 'Payée', 'color' => '#28a745', 'count' => 0, 'total' => 0],
            'PARTIELLE' => ['label' => 'Partielle', 'color' => '#ffc107', 'count' => 0, 'total' => 0],
            'IMPAYEE' => ['label' => 'Impayée', 'color' => '#dc3545', 'count' => 0, 'total' => 0],
            'ANNULEE' => ['label' => 'Annulée', 'color' => '#6c757d', 'count' => 0, 'total' => 0],
        ];

        foreach ($ventes as $vente) {
            if (isset($statuses[$vente->status])) {
                $statuses[$vente->status]['count']++;
                $statuses[$vente->status]['total'] += $vente->totalAfterRemise();
            }
        }

        return array_values($statuses);
    }

    public function loadAnalytics()
    {
        $query = VenteModel::query();
        $this->applyFilters($query);

        // Top clients
        $this->topClients = ClientModel::withCount(['ventes' => function ($q) {
            $this->applyFilters($q);
        }])
            ->orderByDesc('ventes_count')
            ->limit(5)
            ->get()
            ->map(function ($client) {
                // Calculate client's total manually
                $clientVentes = VenteModel::where('client_id', $client->id)
                    ->where(function ($q) {
                        $this->applyFilters($q);
                    })
                    ->with(['ligneVentes', 'paiements'])
                    ->get();

                $totalAmount = 0;
                foreach ($clientVentes as $vente) {
                    $totalAmount += $vente->totalAfterRemise();
                }

                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'ventes_count' => $client->ventes_count,
                    'total_amount' => $totalAmount,
                ];
            })
            ->sortByDesc('total_amount')
            ->values();

        // Status distribution
        $ventes = $query->with(['ligneVentes', 'paiements'])->get();

        $statusStats = [
            'PAYEE' => ['count' => 0, 'total' => 0],
            'PARTIELLE' => ['count' => 0, 'total' => 0],
            'IMPAYEE' => ['count' => 0, 'total' => 0],
            'ANNULEE' => ['count' => 0, 'total' => 0],
        ];

        foreach ($ventes as $vente) {
            if (isset($statusStats[$vente->status])) {
                $statusStats[$vente->status]['count']++;
                $statusStats[$vente->status]['total'] += $vente->totalAfterRemise();
            }
        }

        $this->statusDistribution = collect([
            [
                'status' => 'PAYEE',
                'label' => 'Payée',
                'count' => $statusStats['PAYEE']['count'],
                'total' => $statusStats['PAYEE']['total'],
                'color' => '#28a745'
            ],
            [
                'status' => 'PARTIELLE',
                'label' => 'Partielle',
                'count' => $statusStats['PARTIELLE']['count'],
                'total' => $statusStats['PARTIELLE']['total'],
                'color' => '#ffc107'
            ],
            [
                'status' => 'IMPAYEE',
                'label' => 'Impayée',
                'count' => $statusStats['IMPAYEE']['count'],
                'total' => $statusStats['IMPAYEE']['total'],
                'color' => '#dc3545'
            ],
            [
                'status' => 'ANNULEE',
                'label' => 'Annulée',
                'count' => $statusStats['ANNULEE']['count'],
                'total' => $statusStats['ANNULEE']['total'],
                'color' => '#6c757d'
            ]
        ])->filter(fn($stat) => $stat['count'] > 0)->values();
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'client_id', 'chartType', 'chartPeriod'])) {
            $this->resetPage();
            $this->loadStatistics();
            $this->loadChartData();
            $this->loadAnalytics();

            // Emit event with direct data (not wrapped in array)
            $this->dispatch(
                'chart-updated',
                chartData: $this->chartData,
                chartType: $this->chartType
            );
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'date_from', 'date_to', 'client_id']);
        $this->resetPage();
        $this->loadStatistics();
        $this->loadChartData();
        $this->loadAnalytics();
    }

    // Modal methods
    public function showDetails($venteId)
    {
        $this->selectedVente = VenteModel::with([
            'client',
            'ligneVentes.article',
            'paiements',
            'devise',
            'createdBy',
            'updatedBy'
        ])->find($venteId);

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedVente = null;
    }

    public function render()
    {
        $query = VenteModel::query()
            ->with(['client', 'paiements', 'ligneVentes'])
            ->orderBy('date_facture', 'desc')
            ->orderBy('created_at', 'desc');

        $this->applyFilters($query);

        $ventes = $query->paginate(15);

        // Get clients for filter dropdown
        $clients = ClientModel::orderBy('name')->get();

        view()->share('title', "Historique des Ventes");
        view()->share('breadcrumb', "Historique Ventes");

        return view('livewire.ventes.historique', [
            'ventes' => $ventes,
            'clients' => $clients
        ]);
    }
}
