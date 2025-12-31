<?php

namespace App\Livewire\Ventes;

use Livewire\Component;

class PaiementClient extends Component
{
    public function render()
    {
        view()->share('title', "Paiements clients");
        view()->share('breadcrumb', "Paiements clients");

        return view('livewire.ventes.paiement-client');
    }
}
