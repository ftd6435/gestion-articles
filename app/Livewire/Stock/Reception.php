<?php

namespace App\Livewire\Stock;

use App\Models\Stock\CommandeFournisseur;
use App\Models\Stock\ReceptionFournisseur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class Reception extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $filterCommande = '';
    public $dateFrom;
    public $dateTo;
    public $period = '';

    // Data
    public $commandes = [];

    // Modal
    public $showModal = false;
    public $showDetailsModal = false;
    public $receptionId;
    public $commande_id;
    public $date_reception;
    public $selectedReception;

    protected $listeners = ['confirmDelete'];

    public function mount()
    {
        $this->loadCommandes();
        $this->date_reception = now()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        // Reset pagination when filters change
        if (in_array($propertyName, ['search', 'filterCommande', 'dateFrom', 'dateTo', 'period'])) {
            $this->resetPage();
        }
    }

    public function loadReceptions()
    {
        $query = ReceptionFournisseur::query()
            ->with(['commande.fournisseur', 'ligneReceptions', 'createdBy']);

        // Search by multiple criteria
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($q) use ($searchTerm) {
                // Search by reception reference
                $q->where('reference', 'like', $searchTerm);

                // Search by commande reference
                $q->orWhereHas('commande', function ($q) use ($searchTerm) {
                    $q->where('reference', 'like', $searchTerm);
                });

                // Search by supplier name
                $q->orWhereHas('commande.fournisseur', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
                });
            });
        }

        // Filter by commande
        if ($this->filterCommande) {
            $query->where('commande_id', $this->filterCommande);
        }

        // Date range filter
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('date_reception', [$this->dateFrom, $this->dateTo]);
        }

        // Period shortcuts
        if ($this->period === 'weekly') {
            $query->whereBetween('date_reception', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]);
        } elseif ($this->period === 'monthly') {
            $query->whereMonth('date_reception', now()->month)
                ->whereYear('date_reception', now()->year);
        }

        return $query->latest('date_reception')->paginate(10);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCommande', 'dateFrom', 'dateTo', 'period']);
        $this->resetPage();
    }

    public function loadCommandes()
    {
        // Only load EN_COURS or PARTILLE commands
        $this->commandes = CommandeFournisseur::with('fournisseur')
            ->whereIn('status', ['EN_COURS', 'PARTIELLE'])
            ->orderBy('date_commande', 'desc')
            ->get();
    }

    public function resetForm()
    {
        $this->reset([
            'receptionId',
            'commande_id',
            'date_reception',
        ]);

        $this->date_reception = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function store()
    {
        // Define validation rules
        $this->validate([
            'commande_id' => 'required|exists:commande_fournisseurs,id',
            'date_reception' => 'nullable|date',
        ], [
            'commande_id.required' => 'La commande est obligatoire.',
            'commande_id.exists'   => 'La commande selectionner est invalide.',
            'date_reception.date' => 'Selectionner une date valide',
        ]);

        try {
            if ($this->receptionId) {
                // UPDATE existing reception
                $reception = ReceptionFournisseur::findOrFail($this->receptionId);

                $reception->update([
                    'commande_id' => $this->commande_id,
                    'date_reception' => $this->date_reception,
                    'updated_by' => Auth::id(),
                ]);

                $message = 'Réception modifiée avec succès.';
            } else {
                // CREATE new reception
                ReceptionFournisseur::create([
                    'reference' => 'REC-' . rand(1000, 9999),
                    'commande_id' => $this->commande_id,
                    'date_reception' => $this->date_reception,
                    'created_by' => Auth::id(),
                ]);

                $message = 'Réception créée avec succès.';
            }

            // Reset modal
            $this->closeModal();

            // Refresh data
            $this->loadReceptions();

            session()->flash('success', $message);
        } catch (\Exception $e) {
            session()->flash(
                'error',
                'Une erreur est survenue : ' . $e->getMessage()
            );
        }
    }

    public function create()
    {
        $this->redirectRoute('stock.approvisions.create');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedReception = null;
    }

    public function updatedCommandeId()
    {
        $this->selectCommande();
    }

    public function showDetails($id)
    {
        try {
            $this->selectedReception = ReceptionFournisseur::with([
                'commande.fournisseur',
                'commande.devise',
                'commande.ligneCommandes',
                'ligneReceptions.article',
                'ligneReceptions.magasin',
                'ligneReceptions.etagere',
                'createdBy'
            ])->findOrFail($id);

            $this->showDetailsModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Réception introuvable.');
        }
    }

    public function getStatsProperty()
    {
        $now = now();

        return [
            'today' => ReceptionFournisseur::whereDate('date_reception', $now->toDateString())->count(),

            'weekly' => ReceptionFournisseur::whereBetween('date_reception', [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ])->count(),

            'monthly' => ReceptionFournisseur::whereMonth('date_reception', $now->month)
                ->whereYear('date_reception', $now->year)
                ->count(),

            // 'total_value_monthly' => DB::table('reception_fournisseurs')
            //     ->join('ligne_reception_fournisseurs', 'reception_fournisseurs.id', '=', 'ligne_reception_fournisseurs.reception_id')
            //     ->whereMonth('reception_fournisseurs.date_reception', $now->month)
            //     ->whereYear('reception_fournisseurs.date_reception', $now->year)
            //     ->sum(DB::raw('ligne_reception_fournisseurs.quantitY * ligne_reception_fournisseurs.unit_price')),
        ];
    }

    public function deleteConfirm($id)
    {
        try {
            $reception = ReceptionFournisseur::find($id);

            // Dispatch l'événement avec le nom du commande
            $this->dispatch(
                'confirm-delete',
                id: $id,
                itemName: $reception ? $reception->commande->reference : 'cette reception de commande'
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur lors de la récupération de la reception');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $reception = ReceptionFournisseur::findOrFail($id);
            $reference = $reception->commande->reference;

            $reception->delete();

            $this->loadCommandes();

            // Dispatch événement de succès
            $this->dispatch(
                'delete-success',
                message: "La réception de la commande \"{$reference}\" a été supprimée avec succès."
            );
        } catch (\Exception $e) {
            // Dispatch événement d'erreur
            $this->dispatch(
                'delete-error',
                message: 'Une erreur est survenue lors de la suppression: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.stock.reception', [
            'receptions' => $this->loadReceptions(),
            'title' => 'Gestion des Approvisionnements',
            'breadcrumb' => 'Approvisionnements'
        ]);
    }
}
