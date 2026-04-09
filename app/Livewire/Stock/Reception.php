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

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCommande' => ['except' => ''],
        'paymentStatusFilter' => ['except' => ''],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'period' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // Filters
    public $search = '';
    public $filterCommande = '';
    public $paymentStatusFilter = '';
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
        if (in_array($propertyName, ['search', 'filterCommande', 'paymentStatusFilter', 'dateFrom', 'dateTo', 'period'])) {
            $this->resetPage();
        }
    }

    public function loadReceptions()
    {
        $query = ReceptionFournisseur::query()
            ->with(['commande.fournisseur', 'commande.ligneCommandes', 'ligneReceptions', 'paiements', 'createdBy'])
            ->select('reception_fournisseurs.*')
            ->selectSub(
                DB::table('paiement_fournisseurs')
                    ->selectRaw('COALESCE(SUM(montant), 0)')
                    ->whereColumn('reception_id', 'reception_fournisseurs.id'),
                'total_paid'
            )
            ->selectSub(
                DB::table('ligne_reception_fournisseurs as lr')
                    ->join('ligne_commande_fournisseurs as lc', function ($join) {
                        $join->on('lc.article_id', '=', 'lr.article_id');
                    })
                    ->selectRaw('COALESCE(SUM(lr.quantity * lc.unit_price), 0)')
                    ->whereColumn('lr.reception_id', 'reception_fournisseurs.id')
                    ->whereColumn('lc.commande_id', 'reception_fournisseurs.commande_id'),
                'total_no_discount'
            )
            ->selectSub(
                DB::table('commande_fournisseurs')
                    ->selectRaw('COALESCE(remise, 0)')
                    ->whereColumn('id', 'reception_fournisseurs.commande_id'),
                'remise_percent'
            );

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

        if ($this->paymentStatusFilter) {
            $totalNetExpr = '(total_no_discount - (total_no_discount * (remise_percent / 100)))';

            if ($this->paymentStatusFilter === 'PAYE') {
                $query->havingRaw("total_paid >= {$totalNetExpr}");
            } elseif ($this->paymentStatusFilter === 'PARTIEL') {
                $query->havingRaw("total_paid > 0 AND total_paid < {$totalNetExpr}");
            } elseif ($this->paymentStatusFilter === 'NON_PAYE') {
                $query->havingRaw("total_paid <= 0 AND {$totalNetExpr} > 0");
            }
        }

        return $query->latest('date_reception')->paginate(10);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCommande', 'paymentStatusFilter', 'dateFrom', 'dateTo', 'period']);
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
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($this->receptionId) {
            if (!$currentUser?->canAccess('stock.approvisions', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier des réceptions.');
                return;
            }
        } else {
            if (!$currentUser?->canAccess('stock.approvisions', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer des réceptions.');
                return;
            }
        }

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

                logActivity('Modification d\'une réception', [
                    'old' => [
                        'commande_id' => $reception->commande_id,
                        'date_reception' => $reception->date_reception,
                    ],
                    'new' => [
                        'commande_id' => $this->commande_id,
                        'date_reception' => $this->date_reception,
                    ]
                ], $reception);

                $message = 'Réception modifiée avec succès.';
            } else {
                // CREATE new reception
                $reception = ReceptionFournisseur::create([
                    'reference' => 'REC-' . rand(1000, 9999),
                    'commande_id' => $this->commande_id,
                    'date_reception' => $this->date_reception,
                    'created_by' => Auth::id(),
                ]);

                logActivity('Création d\'une réception', [
                    'reference' => $reception->reference,
                    'commande_id' => $this->commande_id,
                    'date_reception' => $this->date_reception,
                ], $reception);

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
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('stock.approvisions', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer des réceptions.');
            return;
        }

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

            if (!$reception) {
                $this->dispatch(
                    'delete-error',
                    message: 'Réception introuvable.'
                );
                return;
            }

            // Check if reception has paiements
            if ($reception->paiements()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer la réception car elle a été payée."
                );
                return;
            }

            // Check if user is super_admin
            if (Auth::user()->role !== 'super_admin') {
                $this->dispatch(
                    'delete-error',
                    message: "Seul un super administrateur peut supprimer une réception. Tous les articles reçus seront supprimés."
                );
                return;
            }

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

            // Check if reception has paiements
            if ($reception->paiements()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer la réception car elle a été payée."
                );
                return;
            }

            // Check if user is super_admin
            if (Auth::user()->role !== 'super_admin') {
                $this->dispatch(
                    'delete-error',
                    message: "Seul un super administrateur peut supprimer une réception. Tous les articles reçus seront supprimés."
                );
                return;
            }

            logActivity('Suppression d\'une réception', [
                'reference' => $reception->reference,
                'commande_id' => $reception->commande_id,
                'date_reception' => $reception->date_reception,
            ], $reception);

            $reception->delete();

            // Update commande status back to EN_COURS since reception is deleted
            $reception->commande->update(['status' => 'EN_COURS']);

            logActivity('Mise à jour du statut de la commande après suppression de réception', [
                'commande_reference' => $reception->commande->reference,
                'ancien_status' => $reception->commande->getOriginal('status'),
                'nouveau_status' => 'EN_COURS',
            ], $reception->commande);

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
        view()->share('title', "Gestion des Approvisionnements");
        view()->share('breadcrumb', "Approvisionnements");

        return view('livewire.stock.reception', [
            'receptions' => $this->loadReceptions()
        ]);
    }
}
