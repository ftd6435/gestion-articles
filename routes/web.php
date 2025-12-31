<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Livewire\Articles\Articles;
use App\Livewire\Articles\Category;
use App\Livewire\Client;
use App\Livewire\Comptabilite\Devise;
use App\Livewire\Fournisseur;
use App\Livewire\Stock\Commande;
use App\Livewire\Stock\CreateCommande;
use App\Livewire\Stock\CreateReception;
use App\Livewire\Stock\Paiement;
use App\Livewire\Stock\Reception;
use App\Livewire\Ventes\CreateVente;
use App\Livewire\Ventes\Historique;
use App\Livewire\Ventes\Vente;
use App\Livewire\Ventes\VentesJour;
use App\Livewire\Warehouse\Magasin;
use App\Livewire\Warehouse\Etagere;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
});

Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect('/dashboard');
    });

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

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

    // WAREHOUSE ROUTES
    Route::get('/warehouse/magasins', Magasin::class)->name('warehouse.magasins');
    Route::get('/warehouse/etageres', Etagere::class)->name('warehouse.etageres');

    // ROUTE DE LA GESTION DES VENTES
    Route::get('/ventes/ventes', Vente::class)->name('ventes.ventes');
    Route::get('/ventes/create', CreateVente::class)->name('ventes.create');
    Route::get('/ventes/rapports', VentesJour::class)->name('ventes.rapports');
    Route::get('/ventes/historique', Historique::class)->name('ventes.historique');
});
