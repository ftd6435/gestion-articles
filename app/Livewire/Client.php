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
    public $is_default = false;

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
            'is_default' => 'boolean',
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
        $this->is_default = false;
        $this->resetValidation();
    }

    public function create()
    {
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('clients', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer des clients.');
            return;
        }

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
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('clients', 'update')) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier des clients.');
            return;
        }

        try {
            $client = ClientModel::findOrFail($id);

            $this->clientId  = $client->id;
            $this->name      = $client->name;
            $this->telephone = $client->telephone;
            $this->type      = $client->type;
            $this->email     = $client->email;
            $this->adresse   = $client->adresse;
            $this->status    = (bool) $client->status;
            $this->is_default = (bool) $client->is_default;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Client introuvable.');
        }
    }

    /* ===================== STORE ===================== */

    public function storeClient()
    {
        $currentUser = Auth::user();
        if ($this->clientId) {
            if (!$currentUser?->canAccess('clients', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier des clients.');
                return;
            }
        } else {
            if (!$currentUser?->canAccess('clients', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer des clients.');
                return;
            }
        }

        $this->validate();

        try {
            if ($this->is_default) {
                $this->status = true;
            }

            if ($this->clientId) {
                $client = ClientModel::findOrFail($this->clientId);

                $client->update([
                    'name'       => $this->name,
                    'telephone'  => $this->telephone,
                    'type'       => $this->type,
                    'email'      => $this->email,
                    'adresse'    => $this->adresse,
                    'status'     => $this->status,
                    'is_default' => $this->is_default,
                    'updated_by' => Auth::id(),
                ]);

                if ($this->is_default) {
                    ClientModel::where('id', '!=', $client->id)->update(['is_default' => false]);
                }

                logActivity('Modification d\'un client', [
                    'old' => [
                        'name'    => $client->name,
                        'telephone'    => $client->telephone,
                        'type'    => $client->type,
                        'email'    => $client->email,
                        'adresse'    => $client->adresse,
                        'status'    => $client->status,
                        'is_default' => (bool) $client->is_default,
                    ],
                    'new' => [
                        'name'    => $this->name,
                        'telephone'    => $this->telephone,
                        'type'    => $this->type,
                        'email'    => $this->email,
                        'adresse'    => $this->adresse,
                        'status'    => $this->status,
                        'is_default' => (bool) $this->is_default,
                    ]
                ], $client);
            } else {
                $client = ClientModel::create(
                    [
                        'name'       => $this->name,
                        'telephone'  => $this->telephone,
                        'type'       => $this->type,
                        'email'      => $this->email,
                        'adresse'    => $this->adresse,
                        'status'     => $this->status,
                        'is_default' => $this->is_default,
                        'created_by' => Auth::id(),
                    ]
                );

                if ($this->is_default) {
                    ClientModel::where('id', '!=', $client->id)->update(['is_default' => false]);
                }

                logActivity('Création d\'un client', [
                    'name'    => $client->name,
                    'telephone'    => $client->telephone,
                    'type'    => $client->type,
                    'email'    => $client->email,
                    'adresse'    => $client->adresse,
                    'status'    => $client->status,
                    'is_default' => (bool) $client->is_default,
                ], $client);
            }

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
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('clients', 'toggle_status')) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier le statut des clients.');
            return;
        }

        $client = ClientModel::findOrFail($id);

        if ($client->is_default && $client->status) {
            session()->flash('error', 'Impossible de désactiver le client par défaut.');
            return;
        }

        logActivity('Modification du status du client', [
            'name' => $client->name,
            'telephone' => $client->telephone,
            'old_status' => $client->status,
            'new_status' => ! $client->status
        ]);

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

        if (!$client) {
            $this->dispatch(
                'delete-error',
                message: 'Client introuvable.'
            );
            return;
        }

        // Check if client has any ventes (purchases)
        if ($client->ventes()->exists()) {
            $this->dispatch(
                'delete-error',
                message: "Impossible de supprimer le client \"{$client->name}\" car il a des achats enregistrés."
            );
            return;
        }

        logActivity('Demande de suppression d\'un client', [
            'name'    => $client->name,
            'telephone'    => $client->telephone,
            'type'    => $client->type,
            'email'    => $client->email,
            'adresse'    => $client->adresse,
            'status'    => $client->status,
        ], $client);

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

            // Check if client has any ventes (purchases)
            if ($client->ventes()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: "Impossible de supprimer le client \"{$name}\" car il a des achats enregistrés."
                );
                return;
            }

            logActivity('Suppression confirmée d\'un client', [
                'name'    => $client->name,
                'telephone'    => $client->telephone,
                'type'    => $client->type,
                'email'    => $client->email,
                'adresse'    => $client->adresse,
                'status'    => $client->status,
            ], $client);

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

    public function render()
    {
        view()->share('title', "Gestion des clients");
        view()->share('breadcrumb', "Clients");

        return view('livewire.client');
    }
}
