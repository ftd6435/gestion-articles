<?php

namespace App\Livewire\Ventes;

use App\Models\Ventes\VenteModel;
use App\Models\Ventes\VentePaiementClient;
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

    protected $queryString = ['search', 'status', 'date_from', 'date_to'];

    public function mount()
    {
        $this->paiement_date = now()->format('Y-m-d');
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
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

        // Calculate total paid - CORRECT
        $this->totalPaid = VentePaiementClient::whereIn('vente_id', $ventesId)->sum('montant');

        // Calculate total due - also needs correction
        $this->totalDue = $ventes->sum(function ($vente) {
            $total = $vente->totalAfterRemise() ?? 0;
            $paid = $vente->paiements->sum('montant') ?? 0; // Use the loaded collection
            return $total - $paid;
        });
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to'])) {
            $this->resetPage();
            $this->loadStatistics();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'date_from', 'date_to']);
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
        $this->selectedVente = VenteModel::with(['paiements'])->findOrFail($id);
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
        $this->validate([
            'paiement_date' => 'required|date',
            'paiement_montant' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $vente = VenteModel::with('paiements')->find($this->venteId);
                    if ($vente) {
                        $totalPaid = $vente->paiements()->sum('montant');
                        $maxAmount = $vente->totalAfterRemise() - $totalPaid;
                        if ($value > $maxAmount) {
                            $fail("Le montant payé ne peut pas dépasser " . number_format($maxAmount, 2) . " " . ($vente->devise->symbole ?? ''));
                        }
                    }
                }
            ],
            'mode_paiement' => 'required|in:ESPECES,VIREMENT,MOBILE MONEY',
        ]);

        DB::beginTransaction();

        try {
            $vente = VenteModel::with('paiements')->findOrFail($this->venteId);

            VentePaiementClient::create([
                'vente_id' => $vente->id,
                'date_paiement' => $this->paiement_date,
                'montant' => $this->paiement_montant,
                'mode_paiement' => $this->mode_paiement,
                'reference' => 'PAY-' . rand(1000, 9999),
                'notes' => $this->paiement_notes,
                'created_by' => Auth::id(),
            ]);

            // Update vente status
            $totalPaid = $vente->paiements()->sum('montant') + $this->paiement_montant;
            $totalDue = $vente->totalAfterRemise();

            if ($totalPaid >= $totalDue) {
                $vente->status = 'PAYEE';
            } elseif ($totalPaid > 0) {
                $vente->status = 'PARTIELLE';
            } else {
                $vente->status = 'IMPAYEE';
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
        if (!$this->selectedVente) return;

        DB::beginTransaction();

        try {
            $this->selectedVente->update([
                'status' => 'ANNULEE',
                'updated_by' => Auth::id(),
            ]);

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

        DB::beginTransaction();

        try {
            // Delete related records first
            $this->selectedVente->ligneVentes()->delete();
            $this->selectedVente->paiements()->delete();
            $this->selectedVente->delete();

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
        return view('livewire.ventes.vente');
    }
}
