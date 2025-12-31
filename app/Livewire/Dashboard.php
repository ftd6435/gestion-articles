<?php

namespace App\Livewire;

use App\Models\Articles\ArticleModel;
use App\Models\Category;
use App\Models\ClientModel;
use App\Models\DeviseModel;
use App\Models\FournisseurModel;
use App\Models\Stock\CommandeFournisseur;
use App\Models\Stock\LigneCommandeFournisseur;
use App\Models\Stock\LigneReceptionFournisseur;
use App\Models\Stock\PaiementFournisseur;
use App\Models\Stock\ReceptionFournisseur;
use App\Models\Ventes\LigneVenteClient;
use App\Models\Ventes\VenteModel;
use App\Models\Ventes\VentePaiementClient;
use App\Models\Warehouse\MagasinModel;
use App\Models\Warehouse\EtagereModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // Statistics
    public $totalClients = 0;
    public $totalSuppliers = 0;
    public $totalArticles = 0;
    public $totalCategories = 0;
    public $totalOrders = 0;
    public $totalSales = 0;
    public $totalWarehouses = 0;
    public $totalShelves = 0;
    public $totalCurrency = 0;

    // Financials
    public $totalRevenue = 0;
    public $totalPurchases = 0;
    public $totalPaymentsReceived = 0;
    public $totalPaymentsMade = 0;
    public $pendingPayments = 0;
    public $pendingReceivables = 0;

    // Stock
    public $totalStockValue = 0;
    public $lowStockItems = 0;
    public $outOfStockItems = 0;

    // Daily activities
    public $newClientsToday = 0;
    public $newSuppliersToday = 0;
    public $newArticlesToday = 0;
    public $newOrdersToday = 0;
    public $newSalesToday = 0;
    public $newPaymentsToday = 0;

    // Status counts
    public $activeClients = 0;
    public $activeSuppliers = 0;
    public $activeArticles = 0;
    public $pendingOrders = 0;
    public $completedOrders = 0;
    public $pendingSales = 0;
    public $completedSales = 0;

    // Top lists
    public $topClients = [];
    public $topSuppliers = [];
    public $topArticles = [];
    public $topCategories = [];
    public $latestOrders = [];
    public $latestSales = [];
    public $latestPayments = [];
    public $lowStockAlerts = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Basic counts
        $this->totalClients = ClientModel::count();
        $this->totalSuppliers = FournisseurModel::count();
        $this->totalArticles = ArticleModel::count();
        $this->totalCategories = Category::count();
        $this->totalOrders = CommandeFournisseur::count();
        $this->totalSales = VenteModel::count();
        $this->totalWarehouses = MagasinModel::count();
        $this->totalShelves = EtagereModel::count();
        $this->totalCurrency = DeviseModel::count();

        // Financial calculations
        $this->calculateFinancials();

        // Stock calculations
        $this->calculateStock();

        // Daily activities
        $this->calculateDailyActivities();

        // Status counts
        $this->calculateStatusCounts();

        // Top lists
        $this->loadTopLists();

        // Latest activities
        $this->loadLatestActivities();
    }

    private function calculateFinancials()
    {
        // Total Revenue (from sales with discount applied)
        $this->totalRevenue = VenteModel::with('ligneVentes')->get()->sum(function ($vente) {
            $subtotal = $vente->ligneVentes->sum(function ($ligne) {
                return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
            });
            $discount = $subtotal * (($vente->remise ?? 0) / 100);
            return $subtotal - $discount;
        });

        // Total Purchases (from commandes with discount applied)
        $this->totalPurchases = CommandeFournisseur::with('ligneCommandes')->get()->sum(function ($commande) {
            $subtotal = $commande->ligneCommandes->sum(function ($ligne) {
                return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
            });
            $discount = $subtotal * (($commande->remise ?? 0) / 100);
            return $subtotal - $discount;
        });

        // Total Payments Received (from client payments)
        $this->totalPaymentsReceived = VentePaiementClient::sum('montant');

        // Total Payments Made (to suppliers)
        $this->totalPaymentsMade = PaiementFournisseur::sum('montant');

        // Calculate pending receivables (sales not fully paid)
        $this->pendingReceivables = VenteModel::with('ligneVentes', 'paiements')->get()->sum(function ($vente) {
            $total = $vente->totalAfterRemise();
            $paid = $vente->totalPaid();
            return max(0, $total - $paid);
        });

        // Calculate pending payments (receptions not fully paid)
        $this->pendingPayments = ReceptionFournisseur::with('ligneReceptions', 'paiements')->get()->sum(function ($reception) {
            $total = $reception->getTotalAmount();
            $paid = $reception->getTotalPaid();
            return max(0, $total - $paid);
        });
    }

    private function calculateStock()
    {
        // Calculate stock value and alerts
        $articles = ArticleModel::with(['ligneReceptions', 'ligneVentes'])->get();

        $this->totalStockValue = 0;
        $this->lowStockItems = 0;
        $this->outOfStockItems = 0;

        foreach ($articles as $article) {
            $totalReceived = $article->ligneReceptions->sum('quantity') ?? 0;
            $totalSold = $article->ligneVentes->sum('quantity') ?? 0;
            $stock = $totalReceived - $totalSold;

            // Calculate stock value (using purchase price)
            $this->totalStockValue += $stock * ($article->prix_achat ?? 0);

            // Check stock alerts
            if ($stock <= 0) {
                $this->outOfStockItems++;
            } elseif ($stock <= 10) { // Low stock threshold
                $this->lowStockItems++;
            }
        }
    }

    private function calculateDailyActivities()
    {
        $today = now()->startOfDay();

        $this->newClientsToday = ClientModel::whereDate('created_at', $today)->count();
        $this->newSuppliersToday = FournisseurModel::whereDate('created_at', $today)->count();
        $this->newArticlesToday = ArticleModel::whereDate('created_at', $today)->count();
        $this->newOrdersToday = CommandeFournisseur::whereDate('created_at', $today)->count();
        $this->newSalesToday = VenteModel::whereDate('created_at', $today)->count();
        $this->newPaymentsToday = VentePaiementClient::whereDate('date_paiement', $today)
            ->count() + PaiementFournisseur::whereDate('date_paiement', $today)->count();
    }

    private function calculateStatusCounts()
    {
        $this->activeClients = ClientModel::where('status', true)->count();
        $this->activeSuppliers = FournisseurModel::where('status', true)->count();
        $this->activeArticles = ArticleModel::where('status', true)->count();
        $this->pendingOrders = CommandeFournisseur::where('status', 'pending')->count();
        $this->completedOrders = CommandeFournisseur::where('status', 'completed')->count();
        $this->pendingSales = VenteModel::where('status', 'pending')->count();
        $this->completedSales = VenteModel::where('status', 'completed')->count();
    }

    private function loadTopLists()
    {
        // Top 5 Clients by purchase amount
        $this->topClients = ClientModel::with(['ventes' => function ($query) {
            $query->with('ligneVentes');
        }])->get()->map(function ($client) {
            $totalSpent = $client->ventes->sum(function ($vente) {
                return $vente->totalAfterRemise();
            });
            $totalPurchases = $client->ventes->count();

            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->telephone,
                'total_spent' => $totalSpent,
                'total_purchases' => $totalPurchases,
                'avatar' => $this->getAvatar($client->name)
            ];
        })->sortByDesc('total_spent')->take(5)->values();

        // Top 5 Suppliers by order amount
        $this->topSuppliers = FournisseurModel::with(['commandes' => function ($query) {
            $query->with('ligneCommandes');
        }])->get()->map(function ($supplier) {
            $totalSupplied = $supplier->commandes->sum(function ($commande) {
                $subtotal = $commande->ligneCommandes->sum(function ($ligne) {
                    return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
                });
                $discount = $subtotal * (($commande->remise ?? 0) / 100);
                return $subtotal - $discount;
            });
            $totalOrders = $supplier->commandes->count();

            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'email' => $supplier->email,
                'phone' => $supplier->telephone,
                'total_supplied' => $totalSupplied,
                'total_orders' => $totalOrders,
                'avatar' => $this->getAvatar($supplier->name)
            ];
        })->sortByDesc('total_supplied')->take(5)->values();

        // Top 5 Articles by sales quantity
        $this->topArticles = ArticleModel::withCount(['ligneVentes as total_sold'])
            ->with(['ligneVentes' => function ($query) {
                $query->select('article_id', DB::raw('SUM(quantity * unit_price) as revenue'))
                    ->groupBy('article_id');
            }])
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(function ($article) {
                $revenue = $article->ligneVentes->sum('revenue') ?? 0;
                $totalReceived = $article->ligneReceptions->sum('quantity') ?? 0;
                $totalSold = $article->total_sold;

                return [
                    'id' => $article->id,
                    'reference' => $article->reference,
                    'designation' => $article->designation,
                    'category' => $article->category->name ?? '—',
                    'total_sold' => $totalSold,
                    'revenue' => $revenue,
                    'stock' => $totalReceived - $totalSold,
                    'unit' => $article->unite
                ];
            });

        // Top 5 Categories by article count
        $this->topCategories = Category::withCount('articles')
            ->orderByDesc('articles_count')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'article_count' => $category->articles_count,
                    'description' => $category->description
                ];
            });

        // Low stock alerts (articles with stock <= 10)
        $this->lowStockAlerts = ArticleModel::with(['ligneReceptions', 'ligneVentes', 'category'])
            ->get()
            ->filter(function ($article) {
                $totalReceived = $article->ligneReceptions->sum('quantity') ?? 0;
                $totalSold = $article->ligneVentes->sum('quantity') ?? 0;
                $stock = $totalReceived - $totalSold;
                return $stock <= 10 && $stock > 0;
            })
            ->map(function ($article) {
                $totalReceived = $article->ligneReceptions->sum('quantity') ?? 0;
                $totalSold = $article->ligneVentes->sum('quantity') ?? 0;
                $stock = $totalReceived - $totalSold;

                return [
                    'id' => $article->id,
                    'reference' => $article->reference,
                    'designation' => $article->designation,
                    'category' => $article->category->name ?? '—',
                    'stock' => $stock,
                    'unit' => $article->unite
                ];
            })
            ->sortBy('stock')
            ->take(5)
            ->values();
    }

    private function loadLatestActivities()
    {
        // Latest 5 Orders
        $this->latestOrders = CommandeFournisseur::with(['fournisseur', 'ligneCommandes'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $totalAmount = $order->ligneCommandes->sum(function ($ligne) {
                    return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
                });

                if ($order->remise > 0) {
                    $totalAmount = $totalAmount * (1 - ($order->remise / 100));
                }

                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'supplier' => $order->fournisseur->name ?? '—',
                    'amount' => $totalAmount,
                    'status' => $order->status ?? 'pending',
                    'date' => $order->created_at->format('d/m/Y H:i'),
                    'item_count' => $order->ligneCommandes->count()
                ];
            });

        // Latest 5 Sales
        $this->latestSales = VenteModel::with(['client', 'ligneVentes'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                $totalAmount = $sale->totalAfterRemise();
                $totalPaid = $sale->totalPaid();
                $paymentStatus = $totalPaid >= $totalAmount ? 'payé' : ($totalPaid > 0 ? 'partiel' : 'en attente');

                return [
                    'id' => $sale->id,
                    'reference' => $sale->reference,
                    'client' => $sale->client->name ?? '—',
                    'amount' => $totalAmount,
                    'paid' => $totalPaid,
                    'status' => $sale->status ?? 'pending',
                    'payment_status' => $paymentStatus,
                    'date' => $sale->created_at->format('d/m/Y H:i'),
                    'item_count' => $sale->ligneVentes->count()
                ];
            });

        // Latest 5 Payments (combined from clients and suppliers)
        $clientPayments = VentePaiementClient::with(['vente.client'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'client',
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'from_to' => $payment->vente->client->name ?? 'Client',
                    'amount' => $payment->montant,
                    'mode' => $payment->mode_paiement,
                    'date' => Carbon::parse($payment->date_paiement)->format('d/m/Y')
                ];
            });

        $supplierPayments = PaiementFournisseur::with(['commande.fournisseur'])
            ->latest()
            ->limit(2)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'fournisseur',
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'from_to' => $payment->commande->fournisseur->name ?? 'Fournisseur',
                    'amount' => $payment->montant,
                    'mode' => $payment->mode_paiement,
                    'date' => Carbon::parse($payment->date_paiement)->format('d/m/Y')
                ];
            });

        $this->latestPayments = $clientPayments->merge($supplierPayments)
            ->sortByDesc('date')
            ->take(5)
            ->values();
    }

    private function getAvatar($name)
    {
        $initials = '';
        $words = explode(' ', $name);
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) break;
        }

        $colors = ['primary', 'success', 'warning', 'danger', 'info'];
        $colorIndex = crc32($name) % count($colors);

        return [
            'initials' => $initials,
            'color' => $colors[$colorIndex]
        ];
    }

    public function refreshDashboard()
    {
        $this->loadDashboardData();
    }

    public function render()
    {
        view()->share('title', "Tableau de Bord");
        view()->share('breadcrumb', "Accueil");

        return view('livewire.dashboard');
    }
}
