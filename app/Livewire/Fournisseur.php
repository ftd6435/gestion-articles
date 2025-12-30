<?php

namespace App\Livewire;

use App\Models\FournisseurModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Fournisseur extends Component
{
    public $showModal = false;
    public $showDetailsModal = false;
    public $selectedFournisseur = null;

    public $fournisseurs = [];

    public $fournisseurId;
    public $name;
    public $telephone;
    public $adresse;
    public $status = true;


    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:100',

            'telephone' => [
                'required',
                'string',
                'min:6',
                'max:30',
                'regex:/^[0-9]+$/',
                Rule::unique('fournisseur_models', 'telephone')->ignore($this->fournisseurId),
            ],

            'adresse' => 'nullable|string|max:100',
            'status'  => 'boolean',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Le nom du fournisseur est obligatoire.',
            'name.string'   => 'Le nom doit être une chaîne de caractères.',
            'name.min'      => 'Le nom doit contenir au moins 3 caractères.',
            'name.max'      => 'Le nom ne peut pas dépasser 100 caractères.',

            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.string'   => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'telephone.min'      => 'Le numéro de téléphone est trop court.',
            'telephone.max'      => 'Le numéro de téléphone est trop long.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',

            'adresse.string' => 'L’adresse doit être une chaîne de caractères.',
            'adresse.max'    => 'L’adresse ne peut pas dépasser 100 caractères.',

            'status.boolean' => 'Le statut est invalide.',
        ];
    }

    public function mount()
    {
        $this->loadFournisseurs();
    }

    public function loadFournisseurs()
    {
        $this->fournisseurs = FournisseurModel::latest()->get();
    }

    public function resetForm()
    {
        $this->reset([
            'fournisseurId',
            'name',
            'telephone',
            'adresse',
        ]);

        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function showDetails($id)
    {
        $this->selectedFournisseur = FournisseurModel::with(
            'commandes.devise',
            'commandes.ligneCommandes',
            'commandes.receptions',
            'commandes.paiements',
        )->find($id);

        $this->showDetailsModal = true;
    }

    public function closeDetails()
    {
        $this->showDetailsModal = false;
        $this->selectedFournisseur = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function edit($id)
    {
        try {
            $fournisseur = FournisseurModel::findOrFail($id);

            $this->fournisseurId = $fournisseur->id;
            $this->name = $fournisseur->name;
            $this->telephone = $fournisseur->telephone;
            $this->adresse = $fournisseur->adresse;
            $this->status = (bool) $fournisseur->status;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Fournisseur introuvable.');
        }
    }


    public function store()
    {
        $this->validate();

        try {
            FournisseurModel::updateOrCreate(
                ['id' => $this->fournisseurId],
                [
                    'name'       => $this->name,
                    'telephone'  => $this->telephone,
                    'adresse'    => $this->adresse,
                    'status'     => $this->status,
                    'updated_by' => Auth::id(),
                    'created_by' => Auth::id(),
                ]
            );

            $this->loadFournisseurs();
            $this->showModal = false;

            session()->flash(
                'success',
                $this->fournisseurId
                    ? 'Fournisseur modifié avec succès.'
                    : 'Fournisseur créé avec succès.'
            );
        } catch (\Exception $e) {
            session()->flash(
                'error',
                'Une erreur est survenue : ' . $e->getMessage()
            );
        }
    }

    public function toggleStatus($id)
    {
        $fournisseur = FournisseurModel::findOrFail($id);

        $fournisseur->update([
            'status'     => ! $fournisseur->status,
            'updated_by' => Auth::id(),
        ]);

        $this->loadFournisseurs();
        session()->flash('success', 'Statut modifié avec succès.');
    }

    protected $listeners = ['confirmDelete'];

    public function deleteConfirm($id)
    {
        try {
            $fournisseur = FournisseurModel::find($id);

            // Dispatch l'événement avec le nom du fournisseur
            $this->dispatch(
                'confirm-delete',
                id: $id,
                itemName: $fournisseur ? $fournisseur->name : 'ce fournisseur'
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur lors de la récupération du fournisseur');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $fournisseur = FournisseurModel::findOrFail($id);
            $name = $fournisseur->name;

            $fournisseur->delete();

            $this->loadFournisseurs();

            // Dispatch événement de succès
            $this->dispatch(
                'delete-success',
                message: "Le fournisseur \"{$name}\" a été supprimé avec succès."
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
        return view('livewire.fournisseur', [
            'title' => 'Liste des fournisseurs',
            'breadcrumb' => "Fournisseurs"
        ]);
    }
}
