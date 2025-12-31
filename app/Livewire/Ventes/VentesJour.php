<?php

namespace App\Livewire\Ventes;

use App\Models\Ventes\VenteModel;
use Livewire\Component;
use Carbon\Carbon;

class VentesJour extends Component
{
    public $ventes;
    public $totalVentes = 0;
    public $totalPaid = 0;
    public $totalDue = 0;
    public $totalNet = 0;
    public $totalRemise = 0;
    public $selectedPeriode = 'aujourdhui';
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $this->setDateRange();
        $this->loadVentes();
    }

    public function changePeriode($periode)
    {
        $this->selectedPeriode = $periode;
        $this->setDateRange();
        $this->loadVentes();
    }

    protected function setDateRange()
    {
        $today = Carbon::today();

        switch ($this->selectedPeriode) {
            case 'hier':
                $yesterday = $today->copy()->subDay();
                $this->dateFrom = $yesterday->format('Y-m-d');
                $this->dateTo = $yesterday->format('Y-m-d');
                break;
            case 'semaine':
                $this->dateFrom = $today->copy()->startOfWeek()->format('Y-m-d');
                $this->dateTo = $today->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'mois':
                $this->dateFrom = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->dateTo = $today->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'aujourdhui':
            default:
                $this->dateFrom = $today->format('Y-m-d');
                $this->dateTo = $this->dateFrom;
                break;
        }
    }

    public function loadVentes()
    {
        $this->setDateRange();

        $query = VenteModel::query()
            ->whereBetween('date_facture', [$this->dateFrom, $this->dateTo])
            ->whereIn('status', ['PAYEE', 'PARTIELLE', 'IMPAYEE'])
            ->with(['client', 'paiements', 'devise', 'ligneVentes']);

        $this->ventes = $query
            ->orderBy('date_facture', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics from loaded ventes using model methods
        $this->calculateStatistics();
    }

    protected function calculateStatistics()
    {
        $this->totalVentes = $this->ventes->count();

        $this->totalNet = 0;
        $this->totalRemise = 0;
        $this->totalPaid = 0;
        $this->totalDue = 0;

        foreach ($this->ventes as $vente) {
            // Use model methods
            $this->totalNet += $vente->subTotal();
            $this->totalRemise += $vente->discountAmount();
            $this->totalPaid += $vente->totalPaid();
            $this->totalDue += $vente->remainingAmount();
        }
    }

    public function printReport()
    {
        $this->dispatch('print-ventes-jour');
    }

    public function render()
    {
        view()->share('title', "Rapports des ventes");
        view()->share('breadcrumb', "Rapports");

        return view('livewire.ventes.ventes-jour');
    }
}
