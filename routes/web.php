<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Livewire\Articles\Articles;
use App\Livewire\Articles\Category;
use App\Livewire\Comptabilite\Devise;
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

    Route::get('/configuration/categories', Category::class)->name('configuration.categories');
    Route::get('/configuration/devises', Devise::class)->name('configuration.devises');

    // WAREHOUSE ROUTES
    Route::get('/warehouse/magasins', Magasin::class)->name('warehouse.magasins');
    Route::get('/warehouse/etageres', Etagere::class)->name('warehouse.etageres');

    Route::get('/ventes/historique', function () {
        return view('livewire.ventes.historique');
    })->name('ventes.historique');
    Route::get('/ventes/rapports', function () {
        return view('livewire.ventes.rapports');
    })->name('ventes.rapports');
});
