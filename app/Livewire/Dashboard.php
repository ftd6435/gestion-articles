<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $stats = [];
    public $recentSales = [];

    public function mount()
    {
        // Données mock pour les statistiques
        $this->stats = [
            [
                'title' => 'Ventes totales',
                'value' => '45 689 €',
                'change' => '+12.5%',
                'icon' => 'fa-euro-sign',
                'color' => 'indigo'
            ],
            [
                'title' => 'Commandes',
                'value' => '1,234',
                'change' => '+8.2%',
                'icon' => 'fa-shopping-bag',
                'color' => 'green'
            ],
            [
                'title' => 'Clients',
                'value' => '892',
                'change' => '+23.1%',
                'icon' => 'fa-users',
                'color' => 'blue'
            ],
            [
                'title' => 'Articles',
                'value' => '156',
                'change' => '+5.7%',
                'icon' => 'fa-box',
                'color' => 'orange'
            ]
        ];

        // Données mock pour les ventes récentes
        $this->recentSales = [
            ['id' => '#1234', 'client' => 'Jean Dupont', 'montant' => '129.99 €', 'statut' => 'Complété', 'date' => '21/12/2024'],
            ['id' => '#1235', 'client' => 'Marie Martin', 'montant' => '89.50 €', 'statut' => 'En cours', 'date' => '21/12/2024'],
            ['id' => '#1236', 'client' => 'Pierre Bernard', 'montant' => '254.00 €', 'statut' => 'Complété', 'date' => '20/12/2024'],
            ['id' => '#1237', 'client' => 'Sophie Dubois', 'montant' => '45.99 €', 'statut' => 'En attente', 'date' => '20/12/2024'],
            ['id' => '#1238', 'client' => 'Luc Thomas', 'montant' => '178.75 €', 'statut' => 'Complété', 'date' => '19/12/2024']
        ];
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'title' => 'Dashboard',
            'breadcrumb' => null
        ]);
        // ->layout('components.layouts.app',);
    }
}
