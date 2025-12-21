<?php

namespace App\Livewire\Ventes;

use Livewire\Component;

class VentesJour extends Component
{
    public $ventes = [];
    public $statistiques = [];
    public $selectedPeriode = 'aujourdhui';

    public function mount()
    {
        $this->loadStatistiques();
        $this->loadVentes();
    }

    public function loadStatistiques()
    {
        $this->statistiques = [
            'total' => '12,450 €',
            'nombre_ventes' => 47,
            'panier_moyen' => '264.89 €',
            'evolution' => '+15.3%'
        ];
    }

    public function loadVentes()
    {
        $this->ventes = [
            [
                'id' => 'V-2024-001',
                'heure' => '14:35',
                'client' => 'Jean Dupont',
                'produits' => 'Ordinateur Pro, Souris',
                'quantite' => 2,
                'montant' => 1299.99,
                'paiement' => 'Carte bancaire',
                'statut' => 'Validé'
            ],
            [
                'id' => 'V-2024-002',
                'heure' => '14:28',
                'client' => 'Marie Martin',
                'produits' => 'Smartphone X',
                'quantite' => 1,
                'montant' => 899.00,
                'paiement' => 'PayPal',
                'statut' => 'Validé'
            ],
            [
                'id' => 'V-2024-003',
                'heure' => '14:15',
                'client' => 'Pierre Bernard',
                'produits' => 'Casque Audio, Câble USB',
                'quantite' => 2,
                'montant' => 219.98,
                'paiement' => 'Carte bancaire',
                'statut' => 'En cours'
            ],
            [
                'id' => 'V-2024-004',
                'heure' => '13:52',
                'client' => 'Sophie Dubois',
                'produits' => 'Tablette Pro, Stylet',
                'quantite' => 2,
                'montant' => 749.99,
                'paiement' => 'Virement',
                'statut' => 'Validé'
            ],
            [
                'id' => 'V-2024-005',
                'heure' => '13:40',
                'client' => 'Luc Thomas',
                'produits' => 'Clavier mécanique',
                'quantite' => 1,
                'montant' => 159.99,
                'paiement' => 'Carte bancaire',
                'statut' => 'Validé'
            ],
            [
                'id' => 'V-2024-006',
                'heure' => '13:25',
                'client' => 'Claire Petit',
                'produits' => 'Webcam HD, Microphone',
                'quantite' => 2,
                'montant' => 189.98,
                'paiement' => 'PayPal',
                'statut' => 'Annulé'
            ],
            [
                'id' => 'V-2024-007',
                'heure' => '12:58',
                'client' => 'Marc Robert',
                'produits' => 'Écran 27", Support',
                'quantite' => 2,
                'montant' => 459.99,
                'paiement' => 'Carte bancaire',
                'statut' => 'Validé'
            ],
            [
                'id' => 'V-2024-008',
                'heure' => '12:30',
                'client' => 'Emma Laurent',
                'produits' => 'SSD 1TB',
                'quantite' => 1,
                'montant' => 129.99,
                'paiement' => 'Carte bancaire',
                'statut' => 'Validé'
            ]
        ];
    }

    public function updatedSelectedPeriode()
    {
        $this->loadStatistiques();
        $this->loadVentes();
    }

    public function exportExcel()
    {
        // Logique d'export
        $this->dispatch('notify', message: 'Export en cours...');
    }

    public function render()
    {
        return view('livewire.ventes.ventes-jour', [
            'title' => 'Ventes du jour',
            'breadcrumb' => 'Ventes du jour'
        ]);
        // ->layout('components.layouts.app',);
    }
}
