<?php

namespace App\Livewire\Warehouse;

use App\Models\Warehouse\EtagereModel;
use App\Models\Warehouse\MagasinModel;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Etagere extends Component
{
    public $etageres;

    public $etagereId;
    public $code_etagere;
    public $magasin_id;
    public $magasins;
    public $status = true;
    public $showModal = false;

    protected function rules()
    {
        return [
            'code_etagere' => ['required', 'string', 'min:3', Rule::unique('etagere_models', 'code_etagere')->ignore($this->etagereId)],
            'magasin_id' => ['required', 'exists:magasin_models,id'],
            'status' => 'boolean',
        ];
    }

    protected function messages()
    {
        return [
            'code_etagere.required' => 'Le code du magasin est obligatoire',
            'code_etagere.unique' => 'Ce code existe déjà',
            'code_etagere.string' => 'Le code est une chaine de caractère',
            'code_etagere.min' => 'Le code doit contenir au moins 3 caractères',
            'magasin_id.required' => 'Le magasin est obligatoire',
            'magasin_id.exists' => 'Le magasin sélectionné est invalide',
        ];
    }

    public function mount()
    {
        $this->loadEtageres();
        $this->loadMagasins();
        $this->code_etagere = 'ET' . rand(1000, 9999);
    }

    public function loadEtageres()
    {
        $this->etageres = EtagereModel::with('createdBy', 'updatedBy', 'magasin')->latest()->get();
    }

    public function loadMagasins()
    {
        $this->magasins = MagasinModel::with('createdBy', 'updatedBy', 'etageres')->active()->latest()->get();
    }

    public function resetForm()
    {
        $this->reset(['etagereId', 'code_etagere', 'magasin_id']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('warehouse.etageres', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer des étagères.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->loadMagasins();

        $this->resetForm();
    }

    public function edit($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('warehouse.etageres', 'update')) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier des étagères.');
            return;
        }

        try {
            $etagere = EtagereModel::findOrFail($id);

            $this->etagereId = $etagere->id;
            $this->code_etagere = $etagere->code_etagere;
            $this->magasin_id = $etagere->magasin_id;
            $this->status = (bool) $etagere->status;

            $this->loadMagasins();
            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Etagère introuvable');
        }
    }

    public function store()
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($this->etagereId) {
            if (!$currentUser?->canAccess('warehouse.etageres', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier des étagères.');
                return;
            }
        } else {
            if (!$currentUser?->canAccess('warehouse.etageres', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer des étagères.');
                return;
            }
        }

        $this->validate();

        try {
            if ($this->etagereId) {
                // Update existing
                $etagere = EtagereModel::findOrFail($this->etagereId);
                $etagere->update([
                    'code_etagere' => $this->code_etagere,
                    'magasin_id' => $this->magasin_id,
                    'status' => $this->status,
                    'updated_by' => Auth::id(),
                ]);

                $message = 'Etagère modifiée avec succès';

                logActivity('Modification d\'une étagère', [
                    'old' => [
                        'code_etagere' => $etagere->code_etagere,
                        'magasin_id' => $etagere->magasin_id,
                        'status' => $etagere->status,
                    ],
                    'new' => [
                        'code_etagere' => $this->code_etagere,
                        'magasin_id' => $this->magasin_id,
                        'status' => $this->status,
                    ]
                ], $etagere);
            } else {
                // Create new
                $etagere = EtagereModel::create([
                    'code_etagere' => $this->code_etagere,
                    'magasin_id' => $this->magasin_id,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Etagère créée avec succès';

                logActivity('Création d\'une étagère', [
                    'code_etagere' => $this->code_etagere,
                    'magasin_id' => $this->magasin_id,
                    'status' => $this->status,
                ], $etagere);
            }

            $this->loadEtageres();
            $this->closeModal();

            session()->flash('success', $message);
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('warehouse.etageres', 'toggle_status')) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier le statut des étagères.');
            return;
        }

        $etagere = EtagereModel::findOrFail($id);
        $oldStatus = $etagere->status;
        $etagere->update([
            'status' => !$etagere->status,
            'updated_by' => Auth::id(),
        ]);

        logActivity('Modification du statut d\'une étagère', [
            'old_status' => $oldStatus,
            'new_status' => $etagere->status,
            'code_etagere' => $etagere->code_etagere,
        ], $etagere);

        $this->loadEtageres();
        session()->flash('success', 'Statut modifié avec succès');
    }

    protected $listeners = ['confirmDelete'];

    public function deleteConfirm($id)
    {
        $etagere = EtagereModel::find($id);

        if (!$etagere) {
            $this->dispatch(
                'delete-error',
                message: 'Étagère introuvable.'
            );
            return;
        }

        // Check if etagere has any ligneReceptions or ligneVentes
        if ($etagere->ligneReceptions()->exists() || $etagere->ligneVentes()->exists()) {
            $this->dispatch(
                'delete-error',
                message: "Impossible de supprimer l'étagère \"{$etagere->code_etagere}\" car elle contient des réceptions ou ventes."
            );
            return;
        }

        $this->dispatch(
            'confirm-delete',
            id: $id,
            itemName: $etagere ? $etagere->code_etagere : 'cette étagère'
        );
    }

    public function confirmDelete($id)
    {
        try {
            $etagere = EtagereModel::findOrFail($id);
            $code = $etagere->code_etagere;

            // Check if etagere has any ligneReceptions or ligneVentes
            if ($etagere->ligneReceptions()->exists() || $etagere->ligneVentes()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer l'étagère \"{$code}\" car elle contient des réceptions ou ventes."
                );
                return;
            }

            logActivity('Suppression d\'une étagère', [
                'code_etagere' => $etagere->code_etagere,
                'magasin_id' => $etagere->magasin_id,
                'status' => $etagere->status,
            ], $etagere);

            $etagere->delete();

            $this->loadEtageres();

            $this->dispatch(
                'delete-success',
                message: "L'étagère \"{$code}\" a été supprimée avec succès."
            );
        } catch (Exception $e) {
            $this->dispatch(
                'delete-error',
                message: 'Erreur lors de la suppression : ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        view()->share('title', "Gestion des Etagères");
        view()->share('breadcrumb', "Etagères");

        return view('livewire.warehouse.etagere');
    }
}
