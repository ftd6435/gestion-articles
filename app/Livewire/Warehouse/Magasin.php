<?php

namespace App\Livewire\Warehouse;

use App\Models\Warehouse\MagasinModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Magasin extends Component
{
    public $magasins;

    public $magasinId;
    public $code_magasin;
    public $nom;
    public $localisation;
    public $status = true;
    public $showModal = false;

    protected function rules()
    {
        return [
            'code_magasin' => ['required', 'string', 'min:3', Rule::unique('magasin_models', 'code_magasin')->ignore($this->magasinId)],
            'nom' => 'required|string|min:3',
            'localisation' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    protected function messages()
    {
        return [
            'code_magasin.required' => 'Le code du magasin est obligatoire',
            'code_magasin.unique' => 'Ce code existe déjà',
            'code_magasin.string' => 'Le code est une chaine de caractère',
            'code_magasin.min' => 'Le code doit contenir au moins 3 caractères',
            'nom.required' => 'Le nom est obligatoire',
            'nom.string' => 'Le nom est une chaine de caractère',
            'nom.min' => 'Le nom doit contenir au moins 3 caractères',
            'localisation.string' => 'La localisation est une chaine de caractère',
        ];
    }

    public function mount()
    {
        $this->loadMagasins();
    }

    public function loadMagasins()
    {
        $this->magasins = MagasinModel::with('createdBy', 'updatedBy', 'etageres')->latest()->get();
    }

    public function resetForm()
    {
        $this->reset(['magasinId', 'code_magasin', 'nom', 'localisation']);
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
        $this->resetForm();
    }

    public function edit($id)
    {
        try {
            $magasin = MagasinModel::findOrFail($id);

            $this->magasinId = $magasin->id;
            $this->code_magasin = $magasin->code_magasin;
            $this->nom = $magasin->nom;
            $this->localisation = $magasin->localisation;
            $this->status = (bool) $magasin->status;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Magasin introuvable');
        }
    }

    public function store()
    {
        $this->validate();

        try {
            if ($this->magasinId) {
                // Update existing
                $magasin = MagasinModel::findOrFail($this->magasinId);
                $magasin->update([
                    'code_magasin' => $this->code_magasin,
                    'nom' => $this->nom,
                    'localisation' => $this->localisation,
                    'status' => $this->status,
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Magasin modifié avec succès';
            } else {
                // Create new
                MagasinModel::create([
                    'code_magasin' => $this->code_magasin,
                    'nom' => $this->nom,
                    'localisation' => $this->localisation,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Magasin créé avec succès';
            }

            $this->loadMagasins();
            $this->closeModal();

            session()->flash('success', $message);
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $devise = MagasinModel::findOrFail($id);
        $devise->update([
            'status' => !$devise->status,
            'updated_by' => Auth::id(),
        ]);

        $this->loadMagasins();
        session()->flash('success', 'Statut modifié avec succès');
    }

    public function deleteConfirm($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function confirmDelete($id)
    {
        MagasinModel::findOrFail($id)->delete();
        $this->loadMagasins();
    }

    public function render()
    {
        return view('livewire.warehouse.magasin', [
            'title' => "Gestion des Magasins",
            'breadcrumb' => "Magasin"
        ]);
    }
}
