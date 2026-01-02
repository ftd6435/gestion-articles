<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class GlobalSearch extends Component
{
    public $search = '';
    public $results = [];
    public $isOpen = false;

    // Définition de toutes les routes avec leurs informations
    protected $routes = [
        [
            'name' => 'Tableau de bord',
            'route' => 'dashboard',
            'url' => '/dashboard',
            'icon' => 'fas fa-chart-line',
            'description' => 'Vue d\'ensemble de votre activité',
            'category' => 'Tableau de bord'
        ],
        [
            'name' => 'Articles',
            'route' => 'articles',
            'url' => '/articles',
            'icon' => 'fas fa-box',
            'description' => 'Gestion des articles/produits',
            'category' => 'Gestion'
        ],
        [
            'name' => 'Clients',
            'route' => 'clients',
            'url' => '/clients',
            'icon' => 'fas fa-users',
            'description' => 'Gestion de la clientèle',
            'category' => 'Gestion'
        ],
        [
            'name' => 'Fournisseurs',
            'route' => 'fournisseurs',
            'url' => '/fournisseurs',
            'icon' => 'fas fa-truck',
            'description' => 'Gestion des fournisseurs',
            'category' => 'Gestion'
        ],
        [
            'name' => 'Commandes Stock',
            'route' => 'stock.commandes',
            'url' => '/stock/commandes',
            'icon' => 'fas fa-clipboard-list',
            'description' => 'Gestion des commandes de stock',
            'category' => 'Stock'
        ],
        [
            'name' => 'Nouvelle Commande',
            'route' => 'stock.commandes.create',
            'url' => '/stock/commandes/create',
            'icon' => 'fas fa-plus-circle',
            'description' => 'Créer une nouvelle commande',
            'category' => 'Stock'
        ],
        [
            'name' => 'Approvisionnements',
            'route' => 'stock.approvisions',
            'url' => '/stock/approvisions',
            'icon' => 'fas fa-dolly',
            'description' => 'Suivi des approvisionnements',
            'category' => 'Stock'
        ],
        [
            'name' => 'Nouvel Approvisionnement',
            'route' => 'stock.approvisions.create',
            'url' => '/stock/approvisions/create',
            'icon' => 'fas fa-plus-circle',
            'description' => 'Créer un nouvel approvisionnement',
            'category' => 'Stock'
        ],
        [
            'name' => 'Paiements Approvisionnements',
            'route' => 'stock.approvisions.paiements',
            'url' => '/stock/approvisions/paiements',
            'icon' => 'fas fa-money-bill-wave',
            'description' => 'Gestion des paiements d\'approvisionnement',
            'category' => 'Stock'
        ],
        [
            'name' => 'Catégories',
            'route' => 'configuration.categories',
            'url' => '/configuration/categories',
            'icon' => 'fas fa-tags',
            'description' => 'Gestion des catégories d\'articles',
            'category' => 'Configuration'
        ],
        [
            'name' => 'Devises',
            'route' => 'configuration.devises',
            'url' => '/configuration/devises',
            'icon' => 'fas fa-money-bill',
            'description' => 'Gestion des devises',
            'category' => 'Configuration'
        ],
        [
            'name' => 'Magasins',
            'route' => 'warehouse.magasins',
            'url' => '/warehouse/magasins',
            'icon' => 'fas fa-warehouse',
            'description' => 'Gestion des magasins',
            'category' => 'Entrepôt'
        ],
        [
            'name' => 'Étagères',
            'route' => 'warehouse.etageres',
            'url' => '/warehouse/etageres',
            'icon' => 'fas fa-boxes-stacked',
            'description' => 'Gestion des étagères',
            'category' => 'Entrepôt'
        ],
        [
            'name' => 'Mon Profil',
            'route' => 'settings.profile',
            'url' => '/settings/profile',
            'icon' => 'fas fa-user',
            'description' => 'Gérer mon profil utilisateur',
            'category' => 'Paramètres'
        ],
        [
            'name' => 'Ventes',
            'route' => 'ventes.ventes',
            'url' => '/ventes/ventes',
            'icon' => 'fas fa-shopping-cart',
            'description' => 'Gestion des ventes',
            'category' => 'Ventes'
        ],
        [
            'name' => 'Nouvelle Vente',
            'route' => 'ventes.create',
            'url' => '/ventes/create',
            'icon' => 'fas fa-plus-circle',
            'description' => 'Créer une nouvelle vente',
            'category' => 'Ventes'
        ],
        [
            'name' => 'Rapports de Ventes',
            'route' => 'ventes.rapports',
            'url' => '/ventes/rapports',
            'icon' => 'fas fa-chart-bar',
            'description' => 'Rapports et statistiques des ventes',
            'category' => 'Ventes'
        ],
        [
            'name' => 'Historique des Ventes',
            'route' => 'ventes.historique',
            'url' => '/ventes/historique',
            'icon' => 'fas fa-history',
            'description' => 'Historique complet des ventes',
            'category' => 'Ventes'
        ],
    ];

    // Fonction appelée quand la recherche change
    public function updatedSearch($value)
    {
        if (strlen($value) < 2) {
            $this->results = [];
            $this->isOpen = false;
            return;
        }

        $searchTerm = strtolower($value);

        // Filtrer les routes selon le terme de recherche
        $this->results = collect($this->routes)
            ->filter(function ($route) use ($searchTerm) {
                // Recherche dans le nom, description et catégorie
                return str_contains(strtolower($route['name']), $searchTerm) ||
                    str_contains(strtolower($route['description']), $searchTerm) ||
                    str_contains(strtolower($route['category']), $searchTerm) ||
                    str_contains(strtolower($route['route']), $searchTerm);
            })
            ->sortBy('category')
            ->values()
            ->toArray();

        $this->isOpen = !empty($this->results);
    }

    // Fermer les résultats
    public function closeResults()
    {
        $this->isOpen = false;
        $this->search = '';
        $this->results = [];
    }

    // Rediriger vers une route
    public function navigateTo($url)
    {
        $this->closeResults();
        return redirect()->to($url);
    }

    // Gérer les touches du clavier
    public function handleKeydown($event)
    {
        if ($event->key === 'Escape') {
            $this->closeResults();
        }
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
