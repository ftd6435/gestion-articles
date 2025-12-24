<?php

namespace App\Livewire\Stock;

use Carbon\Carbon;
use App\Models\Stock\CommandeFournisseur;
use App\Models\Stock\PaiementFournisseur;
use App\Models\Stock\ReceptionFournisseur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Paiement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    /** ================= SHOW DETAILS ================= */
    public $showDetailsModal = false;
    public $selectedPaiement = null;
    public $selectedPaiementId = null;

    /** ================= FILTERS ================= */
    public $search = '';
    public $period = '';
    public $dateFrom;
    public $dateTo;
    public $filterMode = '';
    public $commandeSearch = '';

    /** ================= FORM ================= */
    public $showModal = false;
    public $paiementId;
    public $symbole = "GNF";

    public $commande_id;
    public $reception_id;
    public $date_paiement;
    public $montant;
    public $mode_paiement = 'ESPECES';
    public $notes;

    /** ================= DATA ================= */
    public $commandes = [];
    public $receptions = [];
    public $selectedReception;

    /** ================= MODES ================= */
    public array $modesPaiement = [
        'ESPECES'   => 'Espèces',
        'CHEQUE'    => 'Chèque',
        'VIREMENT'  => 'Virement',
        'MOBILE'    => 'Mobile Money',
        'CARTE'     => 'Carte bancaire',
    ];

    /** ================= MOUNT ================= */
    public function mount()
    {
        $this->date_paiement = now()->format('Y-m-d');
        $this->loadCommandes();
    }

    /** ================= LOADERS ================= */
    public function loadCommandes()
    {
        $this->commandes = CommandeFournisseur::with([
            'fournisseur',
            'devise',
            'receptions.ligneReceptions',
            'receptions.paiements'
        ])
            ->get()
            ->filter(
                fn($cmd) =>
                $cmd->receptions->contains(
                    fn($r) => !$r->isFullyPaid()
                )
            )
            ->values()
            ->toArray();
    }

    /**
     * Reset payments table filters only
     */
    public function resetFilters()
    {
        // Reset only payments table filters
        $this->reset([
            'search',
            'period',
            'dateFrom',
            'dateTo',
            'filterMode',
        ]);

        // Reset pagination to first page
        $this->resetPage();

        // Optional: Flash success message
        session()->flash('message', 'Filtres réinitialisés avec succès.');
    }

    public function updatedCommandeId()
    {
        $this->reset(['reception_id', 'montant']);
        $this->selectedReception = null;

        if (!$this->commande_id) {
            $this->receptions = [];
            return;
        }

        $this->receptions = ReceptionFournisseur::with([
            'ligneReceptions',
            'paiements',
            'commande.ligneCommandes'
        ])
            ->where('commande_id', $this->commande_id)
            ->get()
            ->filter(fn($r) => !$r->isFullyPaid())
            ->values();
    }

    public function updatedReceptionId()
    {
        $this->selectedReception = ReceptionFournisseur::find($this->reception_id);

        if ($this->selectedReception) {
            $this->montant = $this->selectedReception->getRemainingAmount();
        }
    }

    /** ================= STORE ================= */
    public function store()
    {
        $this->validate([
            'commande_id'   => 'required',
            'reception_id'  => 'required',
            'date_paiement' => 'required|date',
            'montant'       => 'required|numeric|min:0.01',
            'mode_paiement' => 'required',
            'notes' => "nullable|string|min:3"
        ]);

        $reception = ReceptionFournisseur::findOrFail($this->reception_id);
        $remaining = $reception->getRemainingAmount();

        if ($this->paiementId) {
            $paiement = PaiementFournisseur::find($this->paiementId);
            $requiredAmount = $paiement->montant + $remaining;
            $newAmount = $this->montant + $remaining;
        }

        $checkAmount = $this->paiementId ? $newAmount > $requiredAmount : false;

        if ($this->montant > $remaining || $checkAmount) {
            $this->addError(
                'montant',
                'Le montant dépasse le reste à payer.'
            );
            return;
        }

        DB::transaction(function () {
            if ($this->paiementId) {
                $paiement = PaiementFournisseur::find($this->paiementId);

                $paiement->update([
                    'commande_id'   => $this->commande_id,
                    'reception_id'  => $this->reception_id,
                    'date_paiement' => $this->date_paiement,
                    'montant'       => $this->montant,
                    'mode_paiement' => $this->mode_paiement,
                    'notes'         => $this->notes,
                    'updated_by'    => Auth::id(),
                ]);
            } else {
                PaiementFournisseur::create([
                    'commande_id'   => $this->commande_id,
                    'reception_id'  => $this->reception_id,
                    'reference'     => 'PAY-' . rand(1000, 9999),
                    'date_paiement' => $this->date_paiement,
                    'montant'       => $this->montant,
                    'mode_paiement' => $this->mode_paiement,
                    'notes'         => $this->notes,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                ]);
            }
        });

        session()->flash('success', 'Paiement enregistré avec succès.');

        $this->closeModal();
        $this->loadCommandes();
    }

    /** ================= UI ================= */
    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->reset([
            'paiementId',
            'commande_id',
            'reception_id',
            'montant',
            'notes'
        ]);

        $this->date_paiement = now()->format('Y-m-d');
        $this->mode_paiement = 'ESPECES';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function getSymbole()
    {
        if ($this->commande_id) {
            $commande = CommandeFournisseur::with('devise')->findOrFail($this->commande_id);
            $this->symbole = $commande->devise->symbolde ?? $commande->devise->code;
        }
    }

    public function updated($propertyName)
    {
        // Reset page when filters change
        if (in_array($propertyName, ['search', 'period', 'dateFrom', 'dateTo', 'filterMode'])) {
            $this->resetPage();
        }

        // Handle commande and reception updates
        if ($propertyName === 'commande_id') {
            $this->updatedCommandeId();
        }

        if ($propertyName === 'reception_id') {
            $this->updatedReceptionId();
        }
    }

    public function edit($id)
    {
        try {
            $paiement = PaiementFournisseur::findOrFail($id);

            $this->paiementId = $paiement->id;
            $this->commande_id = $paiement->commande_id;
            $this->reception_id = $paiement->reception_id;
            $this->date_paiement = Carbon::parse($paiement->date_paiement)->format('Y-m-d');
            $this->montant = $paiement->montant;
            $this->mode_paiement = $paiement->mode_paiement;
            $this->notes = $paiement->notes;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Paiement introuvable.');
        }
    }

    public function showDetails($id)
    {
        try {
            $this->selectedPaiementId = $id;
            $this->selectedPaiement = PaiementFournisseur::with([
                'commande.fournisseur',
                'commande.devise',
                'reception.ligneReceptions.article',
                'reception.paiements',
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            $this->showDetailsModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Paiement introuvable: ' . $e->getMessage());
        }
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedPaiement = null;
        $this->selectedPaiementId = null;
    }

    protected $listeners = ['confirmDelete'];

    public function deleteConfirm($id)
    {
        try {
            $paiement = PaiementFournisseur::find($id);

            // Dispatch l'événement avec la ref du paiement
            $this->dispatch(
                'confirm-delete',
                id: $id,
                itemName: $paiement ? $paiement->reference : 'ce paiement'
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur lors de la récupération du paiement');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $paiement = PaiementFournisseur::findOrFail($id);
            $reference = $paiement->reference;

            $paiement->delete();

            // Dispatch événement de succès
            $this->dispatch(
                'delete-success',
                message: "Le paiement \"{$reference}\" a été supprimé avec succès."
            );
        } catch (\Exception $e) {
            // Dispatch événement d'erreur
            $this->dispatch(
                'delete-error',
                message: 'Une erreur est survenue lors de la suppression: ' . $e->getMessage()
            );
        }
    }

    /** ================= RENDER ================= */
    public function render()
    {
        $query = PaiementFournisseur::with([
            'commande.fournisseur',
            'commande.devise',
            'reception'
        ]);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhereHas('commande', function ($cq) {
                        $cq->where('reference', 'like', '%' . $this->search . '%')
                            ->orWhereHas('fournisseur', function ($fq) {
                                $fq->where('name', 'like', '%' . $this->search . '%');
                            });
                    });
            });
        }

        // Apply period filter
        if ($this->period === 'today') {
            $query->whereDate('date_paiement', now()->toDateString());
        } elseif ($this->period === 'weekly') {
            $query->whereBetween('date_paiement', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($this->period === 'monthly') {
            $query->whereMonth('date_paiement', now()->month)
                ->whereYear('date_paiement', now()->year);
        }

        // Apply date range filter
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('date_paiement', [
                $this->dateFrom,
                $this->dateTo
            ]);
        } elseif ($this->dateFrom) {
            $query->where('date_paiement', '>=', $this->dateFrom);
        } elseif ($this->dateTo) {
            $query->where('date_paiement', '<=', $this->dateTo);
        }

        // Apply payment mode filter
        if ($this->filterMode) {
            $query->where('mode_paiement', $this->filterMode);
        }

        $paiements = $query->latest('date_paiement')->paginate(10);

        return view('livewire.stock.paiement', [
            'paiements' => $paiements,
            'modesPaiement' => $this->modesPaiement,
        ]);
    }
}
