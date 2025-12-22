<?php

namespace App\Livewire\Comptabilite;

use App\Models\DeviseModel;
use Illuminate\Support\Facades\Auth;
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
                // Ignore le code actuel lors de la mise à jour
                Rule::unique('devise_models', 'code')->ignore($this->deviseId),
            ],
            'libelle' => 'required|string|min:3',
            'symbole' => 'nullable|string|min:1',
            'status' => 'boolean',
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

    protected $listeners = ['confirmDelete'];

    public function mount()
    {
        $this->loadDevises();
    }

    public function loadDevises()
    {
        $this->devises = DeviseModel::with('createdBy', 'updatedBy')->latest()->get();
    }

    public function resetForm()
    {
        $this->reset(['deviseId', 'code', 'libelle', 'symbole']);
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
            $devise = DeviseModel::findOrFail($id);

            $this->deviseId = $devise->id;
            $this->code = $devise->code;
            $this->libelle = $devise->libelle;
            $this->symbole = $devise->symbole;
            $this->status = (bool) $devise->status;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Devise introuvable');
        }
    }

    public function store()
    {
        $this->validate();

        try {
            if ($this->deviseId) {
                // Update existing
                $devise = DeviseModel::findOrFail($this->deviseId);
                $devise->update([
                    'code' => $this->code,
                    'libelle' => $this->libelle,
                    'symbole' => $this->symbole,
                    'status' => $this->status,
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Devise modifiée avec succès';
            } else {
                // Create new
                DeviseModel::create([
                    'code' => $this->code,
                    'libelle' => $this->libelle,
                    'symbole' => $this->symbole,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Devise créée avec succès';
            }

            $this->loadDevises();
            $this->closeModal();

            session()->flash('success', $message);
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $devise = DeviseModel::findOrFail($id);
        $devise->update([
            'status' => !$devise->status,
            'updated_by' => Auth::id(),
        ]);

        $this->loadDevises();
        session()->flash('success', 'Statut modifié avec succès');
    }

    public function deleteConfirm($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function confirmDelete($id)
    {
        DeviseModel::findOrFail($id)->delete();
        $this->loadDevises();
        session()->flash('success', 'Devise supprimée avec succès');
    }

    public function render()
    {
        return view('livewire.comptabilite.devise', [
            'title' => 'Liste des Devises',
            'breadcrumb' => 'Devise'
        ]);
    }
}
