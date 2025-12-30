<?php

namespace App\Livewire;

use App\Models\ClientModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Client extends Component
{
    public $showModal = false;

    public $showDetailsModal = false;
    public $selectedClient = null;

    public $clients = [];

    public $clientId;
    public $name;
    public $type = 'DETAILLANT';
    public $telephone;
    public $email;
    public $adresse;
    public $status = true;

    /* ===================== RULES ===================== */

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
                Rule::unique('client_models', 'telephone')->ignore($this->clientId),
            ],

            'type' => ['required', Rule::in(['GROSSISTE', 'DETAILLANT', 'MIXTE'])],

            'email' => 'nullable|email|max:100',

            'adresse' => 'nullable|string|max:100',

            'status' => 'boolean',
        ];
    }

    /* ===================== MESSAGES ===================== */

    protected function messages()
    {
        return [
            'name.required' => 'Le nom du client est obligatoire.',
            'name.min'      => 'Le nom doit contenir au moins 3 caractères.',
            'name.max'      => 'Le nom ne peut pas dépasser 100 caractères.',

            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex'    => 'Le numéro de téléphone doit contenir uniquement des chiffres.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',

            'type.required' => 'Le type de client est obligatoire.',
            'type.in'       => 'Le type de client est invalide.',

            'email.email' => 'Veuillez fournir une adresse email valide.',
            'email.max'   => 'L’email ne peut pas dépasser 100 caractères.',

            'adresse.max' => 'L’adresse ne peut pas dépasser 100 caractères.',
        ];
    }

    /* ===================== LIFECYCLE ===================== */

    public function mount()
    {
        $this->loadClients();
    }

    public function loadClients()
    {
        $this->clients = ClientModel::latest()->get();
    }

    /* ===================== FORM ===================== */

    public function resetForm()
    {
        $this->reset([
            'clientId',
            'name',
            'telephone',
            'email',
            'adresse',
        ]);

        $this->type = 'DETAILLANT';
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
        $this->selectedClient = ClientModel::with(
            'ventes.devise',
            'ventes.ligneVentes',
            'ventes.paiements',
        )->find($id);

        $this->showDetailsModal = true;
    }

    public function closeDetails()
    {
        $this->showDetailsModal = false;

        $this->selectedClient = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function edit($id)
    {
        try {
            $client = ClientModel::findOrFail($id);

            $this->clientId  = $client->id;
            $this->name      = $client->name;
            $this->telephone = $client->telephone;
            $this->type      = $client->type;
            $this->email     = $client->email;
            $this->adresse   = $client->adresse;
            $this->status    = (bool) $client->status;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Client introuvable.');
        }
    }

    /* ===================== STORE ===================== */

    public function store()
    {
        $this->validate();

        try {
            ClientModel::updateOrCreate(
                ['id' => $this->clientId],
                [
                    'name'       => $this->name,
                    'telephone'  => $this->telephone,
                    'type'       => $this->type,
                    'email'      => $this->email,
                    'adresse'    => $this->adresse,
                    'status'     => $this->status,
                    'updated_by' => Auth::id(),
                    'created_by' => Auth::id(),
                ]
            );

            $this->loadClients();
            $this->showModal = false;

            session()->flash(
                'success',
                $this->clientId
                    ? 'Client modifié avec succès.'
                    : 'Client créé avec succès.'
            );
        } catch (\Exception $e) {
            session()->flash(
                'error',
                'Une erreur est survenue : ' . $e->getMessage()
            );
        }
    }

    /* ===================== STATUS ===================== */

    public function toggleStatus($id)
    {
        $client = ClientModel::findOrFail($id);

        $client->update([
            'status'     => ! $client->status,
            'updated_by' => Auth::id(),
        ]);

        $this->loadClients();
        session()->flash('success', 'Statut modifié avec succès.');
    }

    /* ===================== DELETE ===================== */

    protected $listeners = ['confirmDelete'];

    public function deleteConfirm($id)
    {
        $client = ClientModel::find($id);

        $this->dispatch(
            'confirm-delete',
            id: $id,
            itemName: $client ? $client->name : 'ce client'
        );
    }

    public function confirmDelete($id)
    {
        try {
            $client = ClientModel::findOrFail($id);
            $name = $client->name;

            $client->delete();
            $this->loadClients();

            $this->dispatch(
                'delete-success',
                message: "Le client \"{$name}\" a été supprimé avec succès."
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'delete-error',
                message: 'Erreur lors de la suppression : ' . $e->getMessage()
            );
        }
    }

    /* ===================== VIEW ===================== */

    public function view($id)
    {
        // future: modal or page details
    }

    public function render()
    {
        return view('livewire.client', [
            'title' => 'Liste des clients',
            'breadcrumb' => 'Clients',
        ]);
    }
}
