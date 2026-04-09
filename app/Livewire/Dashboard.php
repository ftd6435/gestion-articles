<?php

namespace App\Livewire;

use App\Models\Articles\ArticleModel;
use App\Models\Category;
use App\Models\ClientModel;
use App\Models\DeviseModel;
use App\Models\FournisseurModel;
use App\Models\Stock\CommandeFournisseur;
use App\Models\Stock\PaiementFournisseur;
use App\Models\Stock\ReceptionFournisseur;
use App\Models\Ventes\VenteModel;
use App\Models\Ventes\VentePaiementClient;
use App\Models\Warehouse\MagasinModel;
use App\Models\Warehouse\EtagereModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // Add this property for currency selection
    public $selectedDeviseId = null;

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
    public $paymentsTodayAmount = 0;
    public $paymentsTodayReceivedAmount = 0;
    public $paymentsTodayPaidAmount = 0;

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

    // Add this for available currencies
    public $availableDevises = [];
    public $currentDevise = null;

    public function mount()
    {
        // Load available currencies
        $this->availableDevises = DeviseModel::active()->get();

        // Set default currency
        $defaultDevise = DeviseModel::getDefaultDevise();
        $this->selectedDeviseId = $defaultDevise ? $defaultDevise->id : null;
        $this->currentDevise = $defaultDevise;

        $this->loadDashboardData();
    }

    // Add this method to handle currency change
    public function updatedSelectedDeviseId()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Get the selected currency or default
        $devise = $this->selectedDeviseId
            ? DeviseModel::find($this->selectedDeviseId)
            : DeviseModel::getDefaultDevise();

        if (!$devise) {
            $devise = DeviseModel::active()->first();
        }

        // Store the selected devise for use in calculations
        $this->selectedDeviseId = $devise ? $devise->id : null;
        $this->currentDevise = $devise;

        // Basic counts - filter by currency
        $this->totalClients = ClientModel::count(); // Clients don't typically have a currency
        $this->totalSuppliers = FournisseurModel::count(); // Suppliers don't typically have a currency
        $this->totalArticles = ArticleModel::where('devise_id', $this->selectedDeviseId)->count();
        $this->totalCategories = Category::count(); // Categories don't have currency
        $this->totalOrders = CommandeFournisseur::where('devise_id', $this->selectedDeviseId)->count();
        $this->totalSales = VenteModel::where('devise_id', $this->selectedDeviseId)->count();
        $this->totalWarehouses = MagasinModel::count(); // Warehouses don't have currency
        $this->totalShelves = EtagereModel::count(); // Shelves don't have currency
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
        // Get the selected currency
        $deviseId = $this->selectedDeviseId;

        // Total Revenue (from sales with discount applied) - filtered by currency
        $this->totalRevenue = VenteModel::where('devise_id', $deviseId)
            ->where('status', '!=', 'ANNULEE')
            ->with('ligneVentes')
            ->get()
            ->sum(function ($vente) {
                $subtotal = $vente->ligneVentes->sum(function ($ligne) {
                    return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
                });
                $discount = $subtotal * (($vente->remise ?? 0) / 100);
                return $subtotal - $discount;
            });

        // Total Purchases (from commandes with discount applied) - filtered by currency
        $this->totalPurchases = CommandeFournisseur::where('devise_id', $deviseId)
            ->where('status', '!=', 'ANNULEE')
            ->with('ligneCommandes')
            ->get()
            ->sum(function ($commande) {
                $subtotal = $commande->ligneCommandes->sum(function ($ligne) {
                    return ($ligne->quantity ?? 0) * ($ligne->unit_price ?? 0);
                });
                $discount = $subtotal * (($commande->remise ?? 0) / 100);
                return $subtotal - $discount;
            });

        // Total Payments Received (from client payments) - filter by currency through sales
        $this->totalPaymentsReceived = VentePaiementClient::whereHas('vente', function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId)->where('status', '!=', 'ANNULEE');
        })->sum('montant');

        // Total Payments Made (to suppliers) - filter by currency through commandes
        $this->totalPaymentsMade = PaiementFournisseur::whereHas('commande', function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId)->where('status', '!=', 'ANNULEE');
        })->sum('montant');

        // Calculate pending receivables (sales not fully paid) - filtered by currency
        $this->pendingReceivables = VenteModel::where('devise_id', $deviseId)
            ->where('status', '!=', 'ANNULEE')
            ->with('ligneVentes', 'paiements')
            ->get()
            ->sum(function ($vente) {
                $total = $vente->totalAfterRemise();
                $paid = $vente->totalPaid();
                return max(0, $total - $paid);
            });

        // Calculate pending payments (receptions not fully paid) - filtered by currency through commandes
        $this->pendingPayments = ReceptionFournisseur::whereHas('commande', function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId)->where('status', '!=', 'ANNULEE');
        })
            ->with('ligneReceptions', 'paiements')
            ->get()
            ->sum(function ($reception) {
                $total = $reception->getTotalAmount();
                $paid = $reception->getTotalPaid();
                return max(0, $total - $paid);
            });
    }

    private function calculateStock()
    {
        $articles = ArticleModel::query()
            ->where('devise_id', $this->selectedDeviseId)
            ->select(['article_models.id', 'article_models.prix_achat'])
            ->selectSub(
                DB::table('ligne_reception_fournisseurs')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('article_id', 'article_models.id'),
                'total_received'
            )
            ->selectSub(
                DB::table('ligne_vente_clients')
                    ->join('vente_models', 'vente_models.id', '=', 'ligne_vente_clients.vente_id')
                    ->selectRaw('COALESCE(SUM(ligne_vente_clients.quantity), 0)')
                    ->whereColumn('ligne_vente_clients.article_id', 'article_models.id')
                    ->where('vente_models.status', '!=', 'ANNULEE'),
                'total_sold'
            )
            ->get();

        $this->totalStockValue = 0;
        $this->lowStockItems = 0;
        $this->outOfStockItems = 0;

        foreach ($articles as $article) {
            $totalReceived = (int) ($article->total_received ?? 0);
            $totalSold = (int) ($article->total_sold ?? 0);
            $stock = max(0, $totalReceived - $totalSold);

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
        $deviseId = $this->selectedDeviseId;

        $this->newClientsToday = ClientModel::whereDate('created_at', $today)->count();
        $this->newSuppliersToday = FournisseurModel::whereDate('created_at', $today)->count();
        $this->newArticlesToday = ArticleModel::where('devise_id', $deviseId)
            ->whereDate('created_at', $today)
            ->count();
        $this->newOrdersToday = CommandeFournisseur::where('devise_id', $deviseId)
            ->where('status', '!=', 'ANNULEE')
            ->whereDate('created_at', $today)
            ->count();
        $this->newSalesToday = VenteModel::where('devise_id', $deviseId)
            ->where('status', '!=', 'ANNULEE')
            ->whereDate('created_at', $today)
            ->count();
        $clientPaymentsTodayQuery = VentePaiementClient::whereHas('vente', function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId)->where('status', '!=', 'ANNULEE');
        })
            ->whereDate('date_paiement', $today)
            ;

        $supplierPaymentsTodayQuery = PaiementFournisseur::whereHas('commande', function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId)->where('status', '!=', 'ANNULEE');
        })
            ->whereDate('date_paiement', $today)
            ;

        $this->newPaymentsToday = $clientPaymentsTodayQuery->count() + $supplierPaymentsTodayQuery->count();
        $this->paymentsTodayReceivedAmount = (float) $clientPaymentsTodayQuery->sum('montant');
        $this->paymentsTodayPaidAmount = (float) $supplierPaymentsTodayQuery->sum('montant');
        $this->paymentsTodayAmount = $this->paymentsTodayReceivedAmount + $this->paymentsTodayPaidAmount;
    }

    private function calculateStatusCounts()
    {
        $deviseId = $this->selectedDeviseId;

        $this->activeClients = ClientModel::where('status', true)->count();
        $this->activeSuppliers = FournisseurModel::where('status', true)->count();
        $this->activeArticles = ArticleModel::where('devise_id', $deviseId)
            ->where('status', true)
            ->count();
        $this->pendingOrders = CommandeFournisseur::where('devise_id', $deviseId)
            ->whereIn('status', ['EN_COURS', 'PARTIELLE'])
            ->count();
        $this->completedOrders = CommandeFournisseur::where('devise_id', $deviseId)
            ->where('status', 'TERMINEE')
            ->count();
        $this->pendingSales = VenteModel::where('devise_id', $deviseId)
            ->whereIn('status', ['IMPAYEE', 'PARTIELLE'])
            ->count();
        $this->completedSales = VenteModel::where('devise_id', $deviseId)
            ->where('status', 'PAYEE')
            ->count();
    }

    private function loadTopLists()
    {
        $deviseId = $this->selectedDeviseId;

        // Top 5 Clients by purchase amount - filter by currency through sales
        $this->topClients = ClientModel::with(['ventes' => function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId)->with('ligneVentes');
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

        // Top 5 Suppliers by order amount - filter by currency through commandes
        $this->topSuppliers = FournisseurModel::with(['commandes' => function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId)->with('ligneCommandes');
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

        // Top 5 Articles by sales quantity - filtered by currency
        $this->topArticles = ArticleModel::query()
            ->where('devise_id', $deviseId)
            ->with('category')
            ->select('article_models.*')
            ->selectSub(
                DB::table('ligne_reception_fournisseurs')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('article_id', 'article_models.id'),
                'total_received'
            )
            ->selectSub(
                DB::table('ligne_vente_clients')
                    ->join('vente_models', 'vente_models.id', '=', 'ligne_vente_clients.vente_id')
                    ->selectRaw('COALESCE(SUM(ligne_vente_clients.quantity), 0)')
                    ->whereColumn('ligne_vente_clients.article_id', 'article_models.id')
                    ->where('vente_models.status', '!=', 'ANNULEE'),
                'total_sold'
            )
            ->selectSub(
                DB::table('ligne_vente_clients')
                    ->join('vente_models', 'vente_models.id', '=', 'ligne_vente_clients.vente_id')
                    ->selectRaw('COALESCE(SUM(ligne_vente_clients.quantity * ligne_vente_clients.unit_price), 0)')
                    ->whereColumn('ligne_vente_clients.article_id', 'article_models.id')
                    ->where('vente_models.status', '!=', 'ANNULEE'),
                'revenue'
            )
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(function ($article) {
                $revenue = (float) ($article->revenue ?? 0);
                $totalReceived = (int) ($article->total_received ?? 0);
                $totalSold = (int) ($article->total_sold ?? 0);
                $stock = max(0, $totalReceived - $totalSold);

                return [
                    'id' => $article->id,
                    'reference' => $article->reference,
                    'designation' => $article->designation,
                    'category' => $article->category->name ?? '—',
                    'total_sold' => $totalSold,
                    'revenue' => $revenue,
                    'stock' => $stock,
                    'unit' => $article->unite
                ];
            });

        // Top 5 Categories by article count - filter articles by currency
        $this->topCategories = Category::withCount(['articles' => function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId);
        }])
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

        // Low stock alerts (articles with stock <= 10) - filtered by currency
        $this->lowStockAlerts = ArticleModel::query()
            ->where('devise_id', $deviseId)
            ->with('category')
            ->select('article_models.*')
            ->selectSub(
                DB::table('ligne_reception_fournisseurs')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('article_id', 'article_models.id'),
                'total_received'
            )
            ->selectSub(
                DB::table('ligne_vente_clients')
                    ->join('vente_models', 'vente_models.id', '=', 'ligne_vente_clients.vente_id')
                    ->selectRaw('COALESCE(SUM(ligne_vente_clients.quantity), 0)')
                    ->whereColumn('ligne_vente_clients.article_id', 'article_models.id')
                    ->where('vente_models.status', '!=', 'ANNULEE'),
                'total_sold'
            )
            ->get()
            ->map(function ($article) {
                $totalReceived = (int) ($article->total_received ?? 0);
                $totalSold = (int) ($article->total_sold ?? 0);
                $stock = max(0, $totalReceived - $totalSold);

                return [
                    'id' => $article->id,
                    'reference' => $article->reference,
                    'designation' => $article->designation,
                    'category' => $article->category->name ?? '—',
                    'stock' => $stock,
                    'unit' => $article->unite
                ];
            })
            ->filter(fn($a) => $a['stock'] <= 10 && $a['stock'] > 0)
            ->sortBy('stock')
            ->take(5)
            ->values();
    }

    private function loadLatestActivities()
    {
        $deviseId = $this->selectedDeviseId;

        // Latest 5 Orders - filtered by currency
        $this->latestOrders = CommandeFournisseur::where('devise_id', $deviseId)
            ->with(['fournisseur', 'ligneCommandes'])
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

        // Latest 5 Sales - filtered by currency
        $this->latestSales = VenteModel::where('devise_id', $deviseId)
            ->with(['client', 'ligneVentes'])
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

        // Latest 5 Payments (combined from clients and suppliers) - filtered by currency
        $clientPayments = VentePaiementClient::whereHas('vente', function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId);
        })
            ->with(['vente.client'])
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

        $supplierPayments = PaiementFournisseur::whereHas('commande', function ($query) use ($deviseId) {
            $query->where('devise_id', $deviseId);
        })
            ->with(['commande.fournisseur'])
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
