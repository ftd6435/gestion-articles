<?php

namespace App\Livewire\Stock;

use App\Models\DeviseModel;
use App\Models\FournisseurModel;
use App\Models\Stock\CommandeFournisseur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Commande extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterStatus = '';
    public $filterFournisseur = '';
    public $dateFrom;
    public $dateTo;
    public $period = ''; // weekly | monthly

    public $fournisseurs = [];
    public $devises = [];

    public $showModal = false;
    public $commandeId;
    public $reference;
    public $fournisseur_id;
    public $devise_id;
    public $taux_change;
    public $remise;
    public $date_commande;
    public $status;

    public function mount()
    {
        $this->loadCommandes();
        $this->loadFournisseurs();
        $this->loadDevises();
    }

    public function loadCommandes()
    {
        $query = CommandeFournisseur::query()
            ->with('fournisseur', 'receptions.ligneReceptions');

        // 🔍 Search (reference + fournisseur)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhereHas('fournisseur', function ($fq) {
                        $fq->where('nom', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // 📌 Status filter
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // 🏢 Fournisseur filter
        if ($this->filterFournisseur) {
            $query->where('fournisseur_id', $this->filterFournisseur);
        }

        // 📅 Date range
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('date_commande', [
                $this->dateFrom,
                $this->dateTo
            ]);
        }

        // 📆 Period filter
        if ($this->period === 'weekly') {
            $query->whereBetween('date_commande', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]);
        }

        if ($this->period === 'monthly') {
            $query->whereMonth('date_commande', now()->month)
                ->whereYear('date_commande', now()->year);
        }

        return $query->latest()->paginate(10);
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'filterStatus',
            'filterFournisseur',
            'dateFrom',
            'dateTo',
            'period',
        ]);

        $this->loadCommandes();
    }

    public function updated()
    {
        $this->loadCommandes();
    }

    public function loadFournisseurs()
    {
        $this->fournisseurs = FournisseurModel::active()->latest()->get();
    }

    public function loadDevises()
    {
        $this->devises = DeviseModel::active()->latest()->get();
    }

    public function resetForm()
    {
        $this->reset([
            'commandeId',
            'reference',
            'fournisseur_id',
            'devise_id',
            'taux_change',
            'remise',
            'date_commande',
            'status',
        ]);

        $this->resetValidation();
    }

    public function create()
    {
        $this->redirectRoute('stock.commandes.create');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function edit($id)
    {
        try {
            $commande = CommandeFournisseur::findOrFail($id);

            $this->commandeId = $commande->id;
            $this->reference = $commande->reference;
            $this->fournisseur_id = $commande->fournisseur->id;
            $this->devise_id = $commande->devise->id;
            $this->taux_change = $commande->taux_change;
            $this->remise = $commande->remise;
            $this->date_commande = Carbon::parse($commande->date_commande)->format('Y-m-d');
            $this->status = $commande->status;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Commande introuvable.');
        }
    }

    public function getStatsProperty()
    {
        return [
            'weekly' => CommandeFournisseur::whereBetween('date_commande', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),

            'monthly' => CommandeFournisseur::whereMonth('date_commande', now()->month)
                ->whereYear('date_commande', now()->year)
                ->count(),
        ];
    }

    public function cancelCommande($id)
    {
        $commande = CommandeFournisseur::findOrFail($id);

        if ($commande->receptions()->exists()) {
            session()->flash('error', "Échec d'annulation, cette commande a déjà enregistré des approvisionnements.");
            return;
        }

        $commande->update(['status' => 'ANNULEE']);
        session()->flash('success', "Commande annulée avec succès");
    }

    public function store()
    {
        // Define validation rules
        $this->validate([
            'reference' => ['required', 'string', 'min:3', Rule::unique('commande_fournisseurs', 'reference')->ignore($this->commandeId)],
            'fournisseur_id' => 'required|exists:fournisseur_models,id',
            'devise_id' => 'required|exists:devise_models,id',
            'taux_change' => 'nullable|numeric|min:0',
            'remise' => 'nullable|numeric|min:0|max:100',
            'date_commande' => 'nullable|date',
        ], [
            'reference.required' => 'La référence de la commande est obligatoire.',
            'reference.string'   => 'La référence de la commande doit être une chaîne de caractères.',
            'reference.min'      => 'La référence de la commande doit contenir au moins :min caractères.',
            'reference.unique'   => 'Cette référence est déjà utilisée par un autre article.',

            'fournisseur_id.required' => 'Le fournisseur est obligatoire.',
            'fournisseur_id.exists'   => 'Le fournisseur selectionner est invalide.',

            'devise_id.required' => 'La devise est obligatoire.',
            'devise_id.exists'   => 'La devise selectionner est invalide.',

            'taux_change' => "Le taux d'échange doit être en numérique",
            'remise' => "La remise doit être en numérique",

            'date_commande.date' => 'Selectionner une date valide',
        ]);

        try {
            if ($this->commandeId) {
                // UPDATE existing command
                $commande = CommandeFournisseur::findOrFail($this->commandeId);

                $commande->update([
                    'fournisseur_id' => $this->fournisseur_id,
                    'devise_id' => $this->devise_id,
                    'reference' => $this->reference,
                    'taux_change' => $this->taux_change ?? 1,
                    'remise' => $this->remise ?? 0,
                    'date_commande' => $this->date_commande,
                    'status' => $this->status,
                    'updated_by' => Auth::id(),
                ]);

                $message = 'Commande modifiée avec succès.';
            } else {
                // CREATE new command
                CommandeFournisseur::create([
                    'fournisseur_id' => $this->fournisseur_id,
                    'devise_id' => $this->devise_id,
                    'reference' => $this->reference,
                    'taux_change' => $this->taux_change ?? 1,
                    'remise' => $this->remise ?? 0,
                    'date_commande' => $this->date_commande ?? now(),
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $message = 'Commande créée avec succès.';
            }

            // Reset modal
            $this->closeModal();

            // Refresh data
            $this->loadCommandes();

            session()->flash('success', $message);
        } catch (\Exception $e) {
            session()->flash(
                'error',
                'Une erreur est survenue : ' . $e->getMessage()
            );
        }
    }

    protected $listeners = ['confirmDelete'];

    public function deleteConfirm($id)
    {
        try {
            $commande = CommandeFournisseur::find($id);

            // Dispatch l'événement avec le nom du commande
            $this->dispatch(
                'confirm-delete',
                id: $id,
                itemName: $commande ? $commande->reference : 'cette commande'
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur lors de la récupération de la commande');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $commande = CommandeFournisseur::findOrFail($id);
            $reference = $commande->reference;

            $commande->delete();

            $this->loadCommandes();

            // Dispatch événement de succès
            $this->dispatch(
                'delete-success',
                message: "La commande \"{$reference}\" a été supprimée avec succès."
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
        return view('livewire.stock.commande', [
            'commandes' => $this->loadCommandes(),
            'title' => "Gestion des Commandes",
            'breadcrumb' => "Commandes"
        ]);
    }
}
