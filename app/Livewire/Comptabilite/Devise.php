<?php

namespace App\Livewire\Comptabilite;

use App\Models\DeviseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Devise extends Component
{
    public $devises;

    public $deviseId;
    public $code;
    public $libelle;
    public $symbole;
    public $status = true;
    public $is_default = false;
    public $showModal = false;

    /**
     * Règles de validation dynamiques
     */
    protected function rules()
    {
        return [
            'code' => [
                'required',
                'string',
                'min:2',
                Rule::unique('devise_models', 'code')->ignore($this->deviseId),
            ],
            'libelle' => 'required|string|min:3',
            'symbole' => 'nullable|string|min:1',
            'status' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    protected function messages()
    {
        return [
            'code.required' => 'Le code est obligatoire',
            'code.unique' => 'Ce code existe déjà',
            'code.min' => 'Le code doit contenir au moins 2 caractères',
            'libelle.required' => 'Le libellé est obligatoire',
            'libelle.min' => 'Le libellé doit contenir au moins 3 caractères',
            'symbole.min' => 'Le symbole doit contenir au moins 1 caractère',
        ];
    }

    protected $listeners = [
        'confirmDelete',
        'confirmToggleStatus',
        'confirmToggleDefault'
    ];

    public function mount()
    {
        $this->loadDevises();
    }

    public function loadDevises()
    {
        $this->devises = DeviseModel::with('createdBy', 'updatedBy')
            ->orderBy('is_default', 'desc')
            ->orderBy('status', 'desc')
            ->latest()
            ->get();
    }

    public function resetForm()
    {
        $this->reset(['deviseId', 'code', 'libelle', 'symbole', 'is_default']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('configuration.devises', 'create')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de créer des devises.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function edit($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('configuration.devises', 'update')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier des devises.');
            return;
        }

        try {
            $devise = DeviseModel::findOrFail($id);

            $this->deviseId = $devise->id;
            $this->code = $devise->code;
            $this->libelle = $devise->libelle;
            $this->symbole = $devise->symbole;
            $this->status = (bool) $devise->status;
            $this->is_default = (bool) $devise->is_default;

            $this->showModal = true;
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Devise introuvable');
        }
    }

    public function store()
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($this->deviseId) {
            if (!$currentUser?->canAccess('configuration.devises', 'update')) {
                $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier des devises.');
                return;
            }
        } else {
            if (!$currentUser?->canAccess('configuration.devises', 'create')) {
                $this->dispatch('error', message: 'Vous n\'avez pas la permission de créer des devises.');
                return;
            }
        }

        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->deviseId) {
                // Update existing
                $devise = DeviseModel::findOrFail($this->deviseId);

                // Get old data before update
                $oldData = $devise->toArray();

                // If setting this as default and it's not already default
                if ($this->is_default && !$devise->is_default) {
                    // Unset any existing default
                    DeviseModel::where('is_default', true)
                        ->where('id', '!=', $devise->id)
                        ->update(['is_default' => false]);
                }

                // If unsetting default, ensure there's at least one default if possible
                if (!$this->is_default && $devise->is_default) {
                    // Find another active devise to set as default
                    $alternative = DeviseModel::where('id', '!=', $devise->id)
                        ->where('status', true)
                        ->first();

                    if ($alternative) {
                        $alternative->update(['is_default' => true]);

                        // Log setting new default
                        logActivity("Définition nouvelle devise par défaut", [
                            'previous_default_id' => $devise->id,
                            'new_default_id' => $alternative->id
                        ], $alternative);
                    }
                }

                $devise->update([
                    'code' => $this->code,
                    'libelle' => $this->libelle,
                    'symbole' => $this->symbole,
                    'status' => $this->status,
                    'is_default' => $this->is_default,
                    'updated_by' => Auth::id(),
                ]);

                $message = 'Devise modifiée avec succès';

                // Log activity with model instance
                logActivity("Modification devise", [
                    'old' => $oldData,
                    'new' => $devise->toArray()
                ], $devise);
            } else {
                // Create new
                // If setting this as default, unset any existing default
                if ($this->is_default) {
                    DeviseModel::where('is_default', true)->update(['is_default' => false]);
                }

                // Ensure inactive devises cannot be set as default
                if ($this->is_default && !$this->status) {
                    $this->is_default = false;
                }

                $devise = DeviseModel::create([
                    'code' => $this->code,
                    'libelle' => $this->libelle,
                    'symbole' => $this->symbole,
                    'status' => $this->status,
                    'is_default' => $this->is_default,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $message = 'Devise créée avec succès';

                // Log activity with model instance
                logActivity("Création devise", [
                    'data' => $devise->toArray()
                ], $devise);
            }

            DB::commit();

            $this->loadDevises();
            $this->closeModal();

            $this->dispatch('success', message: $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Dispatch confirmation for status toggle
     */
    public function toggleStatusConfirm($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('configuration.devises', 'toggle_status')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier le statut des devises.');
            return;
        }

        try {
            $devise = DeviseModel::findOrFail($id);

            $action = $devise->status ? 'désactiver' : 'activer';
            $warning = '';

            // Add warning if deactivating default devise
            if ($devise->status && $devise->is_default) {
                $warning = "En désactivant la devise par défaut, une autre devise active sera automatiquement définie comme défaut.";
            }

            $this->dispatch(
                'confirm-toggle-status',
                id: $id,
                itemName: $devise->libelle,
                action: $action,
                warning: $warning
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status only (active/inactive) - called after confirmation
     */
    public function confirmToggleStatus($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('configuration.devises', 'toggle_status')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier le statut des devises.');
            return;
        }

        try {
            DB::beginTransaction();

            $devise = DeviseModel::findOrFail($id);

            $oldStatus = $devise->status;
            $newStatus = !$devise->status;

            // If deactivating a devise that is default, we need to handle it
            if ($devise->is_default && !$newStatus) {
                // First, find another active devise to set as default
                $alternative = DeviseModel::where('id', '!=', $id)
                    ->where('status', true)
                    ->first();

                if ($alternative) {
                    $alternative->update(['is_default' => true]);

                    // Log setting new default
                    logActivity("Changement devise par défaut (désactivation)", [
                        'previous_default_id' => $devise->id,
                        'previous_default_code' => $devise->code,
                        'new_default_id' => $alternative->id,
                        'new_default_code' => $alternative->code
                    ], $alternative);
                }

                // Unset default for the devise being deactivated
                $devise->is_default = false;
            }

            $devise->update([
                'status' => $newStatus,
                'is_default' => $devise->is_default,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            $this->loadDevises();

            $statusText = $newStatus ? 'activée' : 'désactivée';
            $message = "Devise {$statusText} avec succès";

            // Log activity
            logActivity("Changement statut devise", [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'devise_id' => $devise->id,
                'devise_code' => $devise->code,
                'was_default' => $devise->is_default
            ], $devise);

            $this->dispatch('success', message: $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Dispatch confirmation for default toggle
     */
    public function toggleDefaultConfirm($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('configuration.devises', 'update')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier la devise par défaut.');
            return;
        }

        try {
            $devise = DeviseModel::findOrFail($id);

            // Check if devise is active
            if (!$devise->status) {
                $this->dispatch('error', message: 'Une devise inactive ne peut pas être définie comme devise par défaut.');
                return;
            }

            $action = $devise->is_default ? 'retirer la devise par défaut' : 'définir comme devise par défaut';

            $this->dispatch(
                'confirm-toggle-default',
                id: $id,
                itemName: $devise->libelle,
                action: $action,
                isCurrentDefault: $devise->is_default
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Toggle default status only (make default or remove default) - called after confirmation
     */
    public function confirmToggleDefault($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('configuration.devises', 'update')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier la devise par défaut.');
            return;
        }

        try {
            DB::beginTransaction();

            $devise = DeviseModel::findOrFail($id);

            // Cannot set inactive devise as default
            if (!$devise->status) {
                throw new \Exception('Une devise inactive ne peut pas être définie comme devise par défaut.');
            }

            $oldDefault = $devise->is_default;
            $newDefault = !$oldDefault;

            if ($newDefault) {
                // Setting as default - unset current default
                $currentDefault = DeviseModel::where('is_default', true)
                    ->where('id', '!=', $id)
                    ->first();

                if ($currentDefault) {
                    $currentDefault->update(['is_default' => false]);

                    // Log unsetting previous default
                    logActivity("Désactivation devise par défaut", [
                        'previous_default_id' => $currentDefault->id,
                        'previous_default_code' => $currentDefault->code,
                        'new_default_id' => $devise->id
                    ], $currentDefault);
                }

                $devise->update([
                    'is_default' => true,
                    'updated_by' => Auth::id(),
                ]);

                $message = "Devise '{$devise->libelle}' définie comme devise par défaut";

                // Log setting new default
                logActivity("Définition devise par défaut", [
                    'previous_default_id' => $currentDefault?->id,
                    'new_default_id' => $devise->id,
                    'new_default_code' => $devise->code
                ], $devise);
            } else {
                // Removing default status
                $devise->update([
                    'is_default' => false,
                    'updated_by' => Auth::id(),
                ]);

                $message = "Devise '{$devise->libelle}' n'est plus la devise par défaut";

                // Find another active devise to set as default if available
                $newDefault = DeviseModel::where('id', '!=', $id)
                    ->where('status', true)
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);

                    // Log auto-setting new default
                    logActivity("Définition automatique nouvelle devise par défaut", [
                        'previous_default_id' => $devise->id,
                        'new_default_id' => $newDefault->id,
                        'new_default_code' => $newDefault->code
                    ], $newDefault);

                    $message .= ". La devise '{$newDefault->libelle}' a été définie comme nouvelle devise par défaut.";
                }

                // Log removing default
                logActivity("Retrait devise par défaut", [
                    'devise_id' => $devise->id,
                    'devise_code' => $devise->code,
                    'new_default_set' => isset($newDefault)
                ], $devise);
            }

            DB::commit();

            $this->loadDevises();

            $this->dispatch('success', message: $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Set as default (from button in table) - Deprecated, use toggleDefaultConfirm instead
     */
    public function setAsDefault($id)
    {
        $this->toggleDefaultConfirm($id);
    }

    /**
     * Dispatch confirmation for deletion
     */
    public function deleteConfirm($id)
    {
        try {
            $devise = DeviseModel::find($id);

            if (!$devise) {
                $this->dispatch(
                    'delete-error',
                    message: 'Devise introuvable.'
                );
                return;
            }

            // Prevent deletion if it's the default devise
            if ($devise->is_default) {
                $this->dispatch(
                    'delete-error',
                    message: 'Impossible de supprimer la devise par défaut. Veuillez d\'abord définir une autre devise comme défaut.'
                );
                return;
            }

            // Check if devise has any articles
            if ($devise->articles()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer la devise \"{$devise->libelle}\" car elle est utilisée dans des articles."
                );
                return;
            }

            // Check if devise has any ventes
            if ($devise->ventes()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer la devise \"{$devise->libelle}\" car elle est utilisée dans des ventes."
                );
                return;
            }

            // Dispatch l'événement avec le nom de la devise
            $this->dispatch(
                'confirm-delete',
                id: $id,
                itemName: $devise ? $devise->libelle : 'cette devise'
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur lors de la récupération de la devise: ' . $e->getMessage());
        }
    }

    /**
     * Delete devise after confirmation
     */
    public function confirmDelete($id)
    {
        try {
            DB::beginTransaction();

            $devise = DeviseModel::findOrFail($id);

            // Double-check: Prevent deletion if it's the default devise
            if ($devise->is_default) {
                $this->dispatch(
                    'delete-error',
                    message: 'Impossible de supprimer la devise par défaut.'
                );
                DB::rollBack();
                return;
            }

            // Double-check: Prevent deletion if used in articles
            if ($devise->articles()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer la devise \"{$devise->libelle}\" car elle est utilisée dans des articles."
                );
                DB::rollBack();
                return;
            }

            // Double-check: Prevent deletion if used in ventes
            if ($devise->ventes()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer la devise \"{$devise->libelle}\" car elle est utilisée dans des ventes."
                );
                DB::rollBack();
                return;
            }

            $libelle = $devise->libelle;

            // Log activity before deletion
            logActivity("Suppression devise", [
                'devise_data' => $devise->toArray()
            ], $devise);

            $devise->delete();

            DB::commit();

            $this->loadDevises();

            $this->dispatch(
                'delete-success',
                message: "La devise \"{$libelle}\" a été supprimée avec succès."
            );
        } catch (\Exception $e) {
            DB::rollBack();

            $this->dispatch(
                'delete-error',
                message: 'Une erreur est survenue lors de la suppression: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        view()->share('title', "Liste des Devises");
        view()->share('breadcrumb', "Devises");

        return view('livewire.comptabilite.devise');
    }
}
