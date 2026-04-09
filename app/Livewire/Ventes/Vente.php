<?php

namespace App\Livewire\Ventes;

use App\Models\Ventes\VenteModel;
use App\Models\Ventes\VentePaiementClient;
use App\Models\DeviseModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Vente extends Component
{
    use WithPagination;

    // public $ventes = [];
    public $showDetailsModal = false;
    public $selectedVente;
    public $showPaiementModal = false;
    public $showCancelModal = false;
    public $showDeleteModal = false;

    // Filters
    public $search = '';
    public $status = '';
    public $date_from = '';
    public $date_to = '';
    public $selectedDeviseId = null; // Add currency filter

    // Paiement fields
    public $paiement_date;
    public $paiement_montant = 0;
    public $mode_paiement = 'ESPECES';
    public $paiement_notes;
    public $venteId;

    // Statistics
    public $totalVentes = 0;
    public $totalPaid = 0;
    public $totalDue = 0;
    public $ventesInProgress = 0;

    // Add for currency
    public $availableDevises = [];
    public $currentDevise = null;

    protected $queryString = ['search', 'status', 'date_from', 'date_to', 'selectedDeviseId'];

    public function mount()
    {
        $this->paiement_date = now()->format('Y-m-d');

        // Load available currencies
        $this->availableDevises = DeviseModel::active()->get();

        // Set default currency
        $defaultDevise = DeviseModel::getDefaultDevise();
        $this->selectedDeviseId = $defaultDevise ? $defaultDevise->id : null;
        $this->currentDevise = $defaultDevise;

        $this->loadStatistics();
    }

    // Add this method to handle currency change
    public function updatedSelectedDeviseId()
    {
        $this->resetPage();
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Get current devise
        $this->currentDevise = $this->selectedDeviseId
            ? DeviseModel::find($this->selectedDeviseId)
            : DeviseModel::getDefaultDevise();

        // Create separate queries for different operations
        $baseQuery = VenteModel::query();
        $this->applyFilters($baseQuery);

        // Clone for counts
        $countQuery = clone $baseQuery;
        $this->totalVentes = $countQuery->count();

        // Clone for in-progress count
        $progressQuery = clone $baseQuery;
        $this->ventesInProgress = $progressQuery->whereIn('status', ['IMPAYEE', 'PARTIELLE'])->count();

        // Get filtered ventes for other calculations
        $ventesQuery = clone $baseQuery;
        $ventes = $ventesQuery->with(['ligneVentes', 'paiements'])->get();

        // Get vente IDs from the filtered query
        $idsQuery = clone $baseQuery;
        $ventesId = $idsQuery->pluck('id')->toArray();

        // Calculate total paid
        $this->totalPaid = VentePaiementClient::whereIn('vente_id', $ventesId)->sum('montant');

        // Calculate total due
        $this->totalDue = $ventes->sum(function ($vente) {
            $total = $vente->totalAfterRemise() ?? 0;
            $paid = $vente->paiements->sum('montant') ?? 0;
            return $total - $paid;
        });
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'selectedDeviseId'])) {
            $this->resetPage();
            $this->loadStatistics();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'date_from', 'date_to', 'selectedDeviseId']);
        $this->resetPage();
        $this->loadStatistics();
    }

    private function applyFilters($query)
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->date_from) {
            $query->whereDate('date_facture', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('date_facture', '<=', $this->date_to);
        }

        // Add currency filter
        if ($this->selectedDeviseId) {
            $query->where('devise_id', $this->selectedDeviseId);
        }
    }

    public function getVentesProperty()
    {
        $query = VenteModel::with([
            'client',
            'devise',
            'ligneVentes.article',
            'createdBy',
            'paiements'
        ]);

        $this->applyFilters($query);

        return $query->latest()->paginate(10);
    }

    public function createVente()
    {
        return redirect()->route('ventes.create');
    }

    public function showDetails($id)
    {
        $this->selectedVente = VenteModel::with([
            'client',
            'devise',
            'ligneVentes.article',
            'createdBy',
            'updatedBy',
            'paiements'
        ])->findOrFail($id);

        $this->showDetailsModal = true;
    }

    public function canPaiementModal($id)
    {
        $this->selectedVente = VenteModel::with(['paiements', 'devise'])->findOrFail($id);
        $this->venteId = $id;
        $this->paiement_montant = max(0, $this->selectedVente->totalAfterRemise() - $this->selectedVente->paiements()->sum('montant'));
        $this->showPaiementModal = true;
    }

    public function canCancelVente($id)
    {
        $this->selectedVente = VenteModel::findOrFail($id);

        if ($this->selectedVente->status !== 'IMPAYEE') {
            $this->dispatch(
                'error',
                message: 'Seules les ventes impayées peuvent être annulées.'
            );
            return;
        }

        $this->showCancelModal = true;
    }

    public function canDeleteModal($id)
    {
        $this->selectedVente = VenteModel::findOrFail($id);

        if ($this->selectedVente->status !== 'ANNULEE') {
            $this->dispatch(
                'error',
                message: 'Seules les ventes annulées peuvent être supprimées.'
            );
            return;
        }

        // Check if user is super_admin - use direct check
        if (!Auth::check() || Auth::user()->role !== 'super_admin') {
            $this->dispatch(
                'error',
                message: 'Seuls les super administrateurs peuvent supprimer des ventes.'
            );
            return;
        }

        $this->showDeleteModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedVente = null;
    }

    public function closePaiementModal()
    {
        $this->showPaiementModal = false;
        $this->selectedVente = null;
        $this->venteId = null;
        $this->reset(['paiement_montant', 'mode_paiement', 'paiement_notes']);
    }

    public function storePaiement()
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('ventes.paiements', 'create')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission d\'enregistrer des paiements clients.');
            return;
        }

        $this->validate([
            'paiement_date' => 'required|date',
            'paiement_montant' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $vente = VenteModel::with('devise')->find($this->venteId);
                    if ($vente) {
                        $maxAmount = $vente->remainingAmount();
                        if ($value > $maxAmount) {
                            $currency = $vente->devise ? ($vente->devise->symbole ?? $vente->devise->code) : 'FG';
                            $fail("Le montant payé ne peut pas dépasser " . number_format($maxAmount, 2) . " {$currency}");
                        }
                    }
                }
            ],
            'mode_paiement' => 'required|in:ESPECES,VIREMENT,MOBILE MONEY',
        ]);

        DB::beginTransaction();

        try {
            $vente = VenteModel::findOrFail($this->venteId);

            // Create payment record
            $paiement = VentePaiementClient::create([
                'vente_id' => $vente->id,
                'date_paiement' => $this->paiement_date,
                'montant' => $this->paiement_montant,
                'mode_paiement' => $this->mode_paiement,
                'reference' => 'PAY-' . rand(1000, 9999),
                'notes' => $this->paiement_notes,
                'created_by' => Auth::id(),
            ]);

            // Log payment creation activity
            logActivity(
                'Création de paiment de client',
                [
                    'vente_id' => $vente->id,
                    'vente_reference' => $vente->reference,
                    'montant' => $this->paiement_montant,
                    'mode_paiement' => $this->mode_paiement,
                    'paiement_id' => $paiement->id,
                    'paiement_reference' => $paiement->reference,
                ],
                VentePaiementClient::class
            );

            // REFRESH the model to get fresh data including the new payment
            $vente->refresh();

            $totalPaid = (float) $vente->paiements()->sum('montant');
            $totalDue = (float) $vente->totalAfterRemise();

            $tolerance = 0.01;
            $oldStatus = $vente->status;

            if (abs($totalDue - $totalPaid) <= $tolerance) {
                $vente->status = 'PAYEE';
            } elseif ($totalPaid > 0) {
                $vente->status = 'PARTIELLE';
            } else {
                $vente->status = 'IMPAYEE';
            }

            // Log status change if it changed
            if ($oldStatus !== $vente->status) {
                logActivity(
                    'Modification du status de paiement',
                    [
                        'vente_id' => $vente->id,
                        'vente_reference' => $vente->reference,
                        'old_status' => $oldStatus,
                        'new_status' => $vente->status,
                        'total_paid' => $totalPaid,
                        'total_due' => $totalDue,
                    ],
                    VenteModel::class
                );
            }

            $vente->save();

            DB::commit();

            $this->dispatch(
                'success',
                message: 'Paiement enregistré avec succès.'
            );

            $this->closePaiementModal();
            $this->loadStatistics();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'error',
                message: 'Erreur lors du paiement: ' . $e->getMessage()
            );
        }
    }

    public function cancelVente()
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('ventes.ventes', 'toggle_status')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier le statut des ventes.');
            return;
        }

        if (!$this->selectedVente) return;

        DB::beginTransaction();

        try {
            $oldStatus = $this->selectedVente->status;

            $this->selectedVente->update([
                'status' => 'ANNULEE',
                'updated_by' => Auth::id(),
            ]);

            // Log cancellation activity
            logActivity(
                'Annulation d\'une vente',
                [
                    'vente_id' => $this->selectedVente->id,
                    'vente_reference' => $this->selectedVente->reference,
                    'old_status' => $oldStatus,
                    'new_status' => 'ANNULEE',
                    'action' => 'cancellation',
                    'user_id' => Auth::id(),
                ],
                VenteModel::class
            );

            DB::commit();

            $this->dispatch(
                'success',
                message: 'Vente annulée avec succès.'
            );

            $this->showCancelModal = false;
            $this->selectedVente = null;
            $this->loadStatistics();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'error',
                message: 'Erreur lors de l\'annulation: ' . $e->getMessage()
            );
        }
    }

    public function closeCancelVente()
    {
        $this->showCancelModal = false;
        $this->selectedVente = null;
    }

    public function deleteVente()
    {
        if (!$this->selectedVente) return;

        // Double check super_admin permission - use direct check
        if (!Auth::check() || Auth::user()->role !== 'super_admin') {
            $this->dispatch(
                'delete-error',
                message: 'Permission refusée. Seuls les super administrateurs peuvent supprimer des ventes.'
            );
            $this->showDeleteModal = false;
            return;
        }

        DB::beginTransaction();

        try {
            // Store data for logging before deletion
            $venteData = [
                'id' => $this->selectedVente->id,
                'reference' => $this->selectedVente->reference,
                'client_id' => $this->selectedVente->client_id,
                'client_name' => $this->selectedVente->client?->name,
                'total_amount' => $this->selectedVente->totalAfterRemise(),
                'status' => $this->selectedVente->status,
                'devise_id' => $this->selectedVente->devise_id,
                'ligne_ventes_count' => $this->selectedVente->ligneVentes()->count(),
                'paiements_count' => $this->selectedVente->paiements()->count(),
            ];

            // Delete related records first
            $this->selectedVente->ligneVentes()->delete();
            $this->selectedVente->paiements()->delete();
            $this->selectedVente->delete();

            // Log deletion activity
            logActivity(
                'Suppression d\'une vente',
                $venteData,
                VenteModel::class
            );

            DB::commit();

            $this->dispatch(
                'delete-success',
                message: 'Vente supprimée avec succès.'
            );

            $this->showDeleteModal = false;
            $this->selectedVente = null;
            $this->loadStatistics();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'delete-error',
                message: 'Erreur lors de la suppression: ' . $e->getMessage()
            );
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedVente = null;
        $this->loadStatistics();
    }

    public function render()
    {
        view()->share('title', "Gestion des ventes");
        view()->share('breadcrumb', "Ventes");

        return view('livewire.ventes.vente');
    }
}
