<?php

namespace App\Livewire\Warehouse;

use App\Models\Warehouse\EtagereModel;
use App\Models\Warehouse\MagasinModel;
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
            } else {
                // Create new
                EtagereModel::create([
                    'code_etagere' => $this->code_etagere,
                    'magasin_id' => $this->magasin_id,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Etagère créée avec succès';
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
        $etagere = EtagereModel::findOrFail($id);
        $etagere->update([
            'status' => !$etagere->status,
            'updated_by' => Auth::id(),
        ]);

        $this->loadEtageres();
        session()->flash('success', 'Statut modifié avec succès');
    }

    public function deleteConfirm($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function confirmDelete($id)
    {
        EtagereModel::findOrFail($id)->delete();
        $this->loadEtageres();
    }

    public function render()
    {
        return view('livewire.warehouse.etagere', [
            'title' => 'Liste des Etagères',
            'breadcrumb' => 'Etagères'
        ]);
    }
}
