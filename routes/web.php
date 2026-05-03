<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Livewire\Articles\Articles;
use App\Livewire\Articles\Category;
use App\Livewire\Client;
use App\Livewire\Comptabilite\Operations;
use App\Livewire\Comptabilite\TypeOperations;
use App\Livewire\Comptabilite\Devise;
use App\Livewire\Configuration\CompanySettings;
use App\Livewire\Audit\StockArticle;
use App\Livewire\Audit\Activity;
use App\Livewire\Fournisseur;
use App\Livewire\Profile\UpdateProfile;
use App\Livewire\Stock\Commande;
use App\Livewire\Stock\CreateCommande;
use App\Livewire\Stock\CreateReception;
use App\Livewire\Stock\Paiement;
use App\Livewire\Stock\Reception;
use App\Livewire\Stock\StockInitial;
use App\Livewire\UserManagement;
use App\Livewire\Ventes\CreateVente;
use App\Livewire\Ventes\Historique;
use App\Livewire\Ventes\Vente;
use App\Livewire\Ventes\VentesJour;
use App\Livewire\Warehouse\Magasin;
use App\Livewire\Warehouse\Etagere;
use App\Livewire\Legacy\ClientDebts as LegacyClientDebts;
use App\Livewire\Legacy\FournisseurDebts as LegacyFournisseurDebts;
use App\Livewire\Legacy\Reports as LegacyReports;

Route::get('/public/commandes/{commande}', function (\App\Models\Stock\CommandeFournisseur $commande) {
    $commande->load([
        'devise',
        'fournisseur',
        'ligneCommandes.article',
        'receptions.ligneReceptions.article',
        'paiements',
        'createdBy',
    ]);

    return view('public.commande', ['selectedCommande' => $commande]);
})->middleware('signed')->name('public.commandes.show');

Route::get('/public/ventes/{vente}', function (\App\Models\Ventes\VenteModel $vente) {
    $vente->load([
        'devise',
        'client',
        'ligneVentes.article',
        'paiements',
        'createdBy',
        'updatedBy',
    ]);

    return view('public.vente', ['selectedVente' => $vente]);
})->middleware('signed')->name('public.ventes.show');

Route::get('/public/receptions/{reception}', function (\App\Models\Stock\ReceptionFournisseur $reception) {
    $reception->load([
        'commande.devise',
        'commande.fournisseur',
        'commande.ligneCommandes',
        'ligneReceptions.article',
        'ligneReceptions.magasin',
        'ligneReceptions.etagere',
        'paiements',
        'createdBy',
    ]);

    $modesPaiement = [
        'ESPECES' => 'Espèces',
        'CHEQUE' => 'Chèque',
        'VIREMENT' => 'Virement',
        'MOBILE' => 'Mobile Money',
        'CARTE' => 'Carte bancaire',
    ];

    return view('public.reception', ['selectedReception' => $reception, 'modesPaiement' => $modesPaiement]);
})->middleware('signed')->name('public.receptions.show');

Route::get('/public/paiements/{paiement}', function (\App\Models\Stock\PaiementFournisseur $paiement) {
    $paiement->load([
        'commande.fournisseur',
        'commande.devise',
        'commande.ligneCommandes',
        'reception.ligneReceptions.article',
        'reception.paiements',
        'createdBy',
        'updatedBy',
    ]);

    $modesPaiement = [
        'ESPECES' => 'Espèces',
        'CHEQUE' => 'Chèque',
        'VIREMENT' => 'Virement',
        'MOBILE' => 'Mobile Money',
        'CARTE' => 'Carte bancaire',
    ];

    return view('public.paiement', ['selectedPaiement' => $paiement, 'modesPaiement' => $modesPaiement]);
})->middleware('signed')->name('public.paiements.show');

Route::get('/public/rapports/ventes-jour', function (\Illuminate\Http\Request $request) {
    $periode = (string) $request->query('periode', 'aujourdhui');
    $allowedPeriodes = ['aujourdhui', 'hier', 'semaine', 'mois'];
    if (!in_array($periode, $allowedPeriodes, true)) {
        $periode = 'aujourdhui';
    }

    $dateFrom = $request->query('dateFrom');
    $dateTo = $request->query('dateTo');

    if (!$dateFrom || !$dateTo) {
        $now = now();
        if ($periode === 'hier') {
            $dateFrom = $now->copy()->subDay()->startOfDay()->toDateString();
            $dateTo = $now->copy()->subDay()->endOfDay()->toDateString();
        } elseif ($periode === 'semaine') {
            $dateFrom = $now->copy()->startOfWeek()->toDateString();
            $dateTo = $now->copy()->endOfWeek()->toDateString();
        } elseif ($periode === 'mois') {
            $dateFrom = $now->copy()->startOfMonth()->toDateString();
            $dateTo = $now->copy()->endOfMonth()->toDateString();
        } else {
            $dateFrom = $now->copy()->startOfDay()->toDateString();
            $dateTo = $now->copy()->endOfDay()->toDateString();
        }
    }

    $deviseId = $request->query('devise_id');
    if (!$deviseId) {
        $defaultDevise = \App\Models\DeviseModel::getDefaultDevise();
        $deviseId = $defaultDevise?->id;
    }

    $venteQuery = \App\Models\Ventes\VenteModel::query()
        ->where('devise_id', $deviseId)
        ->whereIn('status', ['PAYEE', 'PARTIELLE', 'IMPAYEE'])
        ->whereBetween('date_facture', [$dateFrom, $dateTo])
        ->with(['client', 'ligneVentes.article', 'paiements'])
        ->orderByDesc('date_facture');

    $ventes = $venteQuery->get();

    $totalVentes = $ventes->count();
    $totalMontant = 0;
    $totalPayee = 0;
    $totalReste = 0;
    $totalArticles = 0;

    foreach ($ventes as $vente) {
        $total = $vente->totalAfterRemise();
        $paid = $vente->totalPaid();
        $reste = max(0, $total - $paid);

        $totalMontant += $total;
        $totalPayee += $paid;
        $totalReste += $reste;
        $totalArticles += (int) $vente->ligneVentes->sum('quantity');
    }

    $devise = \App\Models\DeviseModel::find($deviseId);
    $currency = $devise?->symbole ?? $devise?->code ?? 'FG';

    $periodeLabels = [
        'aujourdhui' => "Aujourd'hui",
        'hier' => 'Hier',
        'semaine' => 'Cette semaine',
        'mois' => 'Ce mois',
    ];

    return view('public.ventes-jour', [
        'selectedPeriode' => $periode,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'currency' => $currency,
        'periodeLabels' => $periodeLabels,
        'ventes' => $ventes,
        'totalVentes' => $totalVentes,
        'totalMontant' => $totalMontant,
        'totalPayee' => $totalPayee,
        'totalReste' => $totalReste,
        'totalArticles' => $totalArticles,
    ]);
})->middleware('signed')->name('public.ventesjour.show');

Route::get('/public/stock-initial', function (\Illuminate\Http\Request $request) {
    $search = $request->query('search', '');
    $magasinId = $request->query('magasin_id');
    $etagereId = $request->query('etagere_id');
    $expirable = $request->query('expirable');

    $query = \App\Models\Stock\StockInitialArticle::query()
        ->with(['article', 'magasin', 'etagere'])
        ->orderBy('date_inventaire', 'desc')
        ->orderBy('created_at', 'desc');

    if ($search) {
        $query->whereHas('article', function ($q) use ($search) {
            $q->where('reference', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%");
        });
    }

    if ($magasinId) {
        $query->where('magasin_id', $magasinId);
    }

    if ($etagereId) {
        $query->where('etagere_id', $etagereId);
    }

    if ($expirable !== null && $expirable !== '') {
        if ($expirable === '1') {
            $query->whereNotNull('date_expiration');
        } elseif ($expirable === '0') {
            $query->whereNull('date_expiration');
        }
    }

    $stocks = $query->get();

    // Build filters array for display
    $filters = [];
    if ($search) {
        $filters['search'] = $search;
    }
    if ($magasinId) {
        $magasin = \App\Models\Warehouse\MagasinModel::find($magasinId);
        $filters['magasin'] = $magasin?->nom ?? 'ID: ' . $magasinId;
    }
    if ($etagereId) {
        $etagere = \App\Models\Warehouse\EtagereModel::find($etagereId);
        $filters['etagere'] = $etagere?->code_etagere ?? 'ID: ' . $etagereId;
    }

    return view('public.stock-initial', [
        'stocks' => $stocks,
        'filters' => $filters,
    ]);
})->middleware('signed')->name('public.stock-initial.show');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    //     ->name('password.request');

    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    //     ->name('password.email');
});

Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Route::get('settings/register', [RegisteredUserController::class, 'create'])
//     ->middleware('auth')
//     ->name('settings.register');

// Route::post('register', [RegisteredUserController::class, 'store'])
//     ->middleware('auth')
//     ->name('register.store');


Route::middleware(['auth', 'access'])->group(function () {
    Route::get('/', function () {
        return redirect('/dashboard');
    });

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/articles/stock-initial', StockInitial::class)->name('articles.stock-initial');
    Route::get('/articles', Articles::class)->name('articles');
    Route::get('/clients', Client::class)->name('clients');
    Route::get('/fournisseurs', Fournisseur::class)->name('fournisseurs');

    Route::get('/stock/commandes', Commande::class)->name('stock.commandes');
    Route::get('/stock/commandes/create', CreateCommande::class)->name('stock.commandes.create');

    Route::get('/stock/approvisions', Reception::class)->name('stock.approvisions');
    Route::get('/stock/approvisions/create', CreateReception::class)->name('stock.approvisions.create');
    Route::get('/stock/approvisions/paiements', Paiement::class)->name('stock.approvisions.paiements');

    Route::get('/configuration/categories', Category::class)->name('configuration.categories');
    Route::get('/configuration/devises', Devise::class)->name('configuration.devises');
    Route::get('/configuration/settings', CompanySettings::class)->name('configuration.settings');

    Route::get('/comptabilite/types-operations', TypeOperations::class)->name('comptabilite.types-operations');
    Route::get('/comptabilite/operations', Operations::class)->name('comptabilite.operations');

    Route::get('/audit/stock-article', StockArticle::class)->name('audit.stock-article');
    Route::get('/audit/activity', Activity::class)->name('audit.activity');

    Route::get('/anciens/clients', LegacyClientDebts::class)->name('legacy.clients');
    Route::get('/anciens/fournisseurs', LegacyFournisseurDebts::class)->name('legacy.fournisseurs');
    Route::get('/anciens/rapports', LegacyReports::class)->name('legacy.reports');

    // WAREHOUSE ROUTES
    Route::get('/warehouse/magasins', Magasin::class)->name('warehouse.magasins');
    Route::get('/warehouse/etageres', Etagere::class)->name('warehouse.etageres');

    // AUTH USER PROFILE
    Route::get('/settings/profile', UpdateProfile::class)->name('settings.profile');
    Route::get('/settings/users', UserManagement::class)->name('settings.users');

    // ROUTE DE LA GESTION DES VENTES
    Route::get('/ventes/ventes', Vente::class)->name('ventes.ventes');
    Route::get('/ventes/create', CreateVente::class)->name('ventes.create');
    Route::get('/ventes/rapports', VentesJour::class)->name('ventes.rapports');
    Route::get('/ventes/historique', Historique::class)->name('ventes.historique');
});
