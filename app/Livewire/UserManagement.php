<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $confirmingStatusChange = null;
    public $confirmingRoleChange = null;
    public $newRole = '';
    public $showCreateModal = false;

    // New user properties
    public $name = '';
    public $email = '';
    public $telephone = '';
    public $role = 'user';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'newRole' => 'required|in:admin,user',
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'telephone' => 'nullable|string|max:20',
        'role' => 'required|in:super_admin,admin,user',
        'password' => 'required|string|min:8|confirmed',
        'password_confirmation' => 'required|string',
    ];

    protected $messages = [
        // newRole messages
        'newRole.required' => 'La sélection du rôle est obligatoire pour la modification.',
        'newRole.in' => 'Le rôle doit être soit administrateur, soit utilisateur.',

        // name messages
        'name.required' => 'Veuillez saisir le nom de l\'utilisateur.',
        'name.string' => 'Le nom doit être composé de lettres.',
        'name.max' => 'Le nom est trop long (maximum 255 caractères).',

        // email messages
        'email.required' => 'L\'adresse email est requise.',
        'email.string' => 'L\'email doit être une chaîne de caractères.',
        'email.email' => 'Format d\'email invalide. Exemple: utilisateur@exemple.com',
        'email.max' => 'L\'email est trop long (maximum 255 caractères).',
        'email.unique' => 'Un compte existe déjà avec cette adresse email.',

        // telephone messages
        'telephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
        'telephone.max' => 'Le numéro de téléphone est trop long (maximum 20 caractères).',

        // role messages
        'role.required' => 'Veuillez attribuer un rôle à l\'utilisateur.',
        'role.in' => 'Le rôle doit être: super administrateur, administrateur ou utilisateur.',

        // password messages
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
        'password.min' => 'Le mot de passe doit contenir minimum 8 caractères.',
        'password.confirmed' => 'Les mots de passe saisis ne sont pas identiques.',

        // password_confirmation messages
        'password_confirmation.required' => 'Veuillez confirmer le mot de passe.',
        'password_confirmation.string' => 'La confirmation doit être une chaîne de caractères.',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin() && !$currentUser->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function render()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $users = User::when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('telephone', 'like', '%' . $this->search . '%');
            });
        })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        view()->share('title', "Gestion des Utilisateurs");
        view()->share('breadcrumb', "Utilisateurs");

        return view('livewire.user-management', [
            'users' => $users,
            'currentUser' => $currentUser,
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['name', 'email', 'telephone', 'role', 'password', 'password_confirmation']);
        $this->resetErrorBag();
    }

    public function createUser()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Only super_admin can create super_admin or admin users
        if ($this->role !== 'user' && !$currentUser->isSuperAdmin()) {
            $this->dispatch('error', message: 'Seul le super administrateur peut créer des administrateurs.');
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|in:super_admin,admin,user',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        try {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'telephone' => $this->telephone,
                'role' => $this->role,
                'password' => Hash::make($this->password),
                'status' => true,
            ]);

            logActivity('Création d\'un utilisateur', [
                'name' => $this->name,
                'email' => $this->email,
                'telephone' => $this->telephone,
                'role' => $this->role,
            ], $user);

            $this->dispatch('success', message: 'Utilisateur créé avec succès.');
            $this->closeCreateModal();
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
        }
    }

    public function confirmStatusChange($userId)
    {
        $this->confirmingStatusChange = $userId;
    }

    public function toggleUserStatus($userId)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        $userToUpdate = User::findOrFail($userId);

        // Check permissions: Only super_admin or admin can enable/disable users
        if (!$currentUser->isSuperAdmin() && !$currentUser->isAdmin()) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de modifier le statut des utilisateurs.');
            return;
        }

        // Prevent users from disabling themselves
        if ($userToUpdate->id === $currentUser->id) {
            $this->dispatch('error', message: 'Vous ne pouvez pas désactiver votre propre compte.');
            return;
        }

        // Prevent non-super_admins from modifying super_admin accounts
        if ($userToUpdate->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            $this->dispatch('error', message: 'Vous ne pouvez pas modifier le statut d\'un super administrateur.');
            return;
        }

        $userToUpdate->status = !$userToUpdate->status;
        $userToUpdate->save();

        logActivity('Modification du statut d\'un utilisateur', [
            'user_id' => $userToUpdate->id,
            'user_name' => $userToUpdate->name,
            'old_status' => !$userToUpdate->status,
            'new_status' => $userToUpdate->status,
        ], $userToUpdate);

        $statusText = $userToUpdate->status ? 'activé' : 'désactivé';
        $this->dispatch('success', message: "Utilisateur {$statusText} avec succès.");

        $this->confirmingStatusChange = null;
    }

    public function confirmRoleChange($userId, $currentRole)
    {
        $this->confirmingRoleChange = $userId;
        $this->newRole = $currentRole === 'super_admin' ? 'admin' : $currentRole;
    }

    public function updateUserRole($userId)
    {
        $this->validate(['newRole' => 'required|in:admin,user']);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        $userToUpdate = User::findOrFail($userId);

        // Check permissions: Only super_admin can change roles
        if (!$currentUser->isSuperAdmin()) {
            $this->dispatch('error', message: 'Seul le super administrateur peut modifier les rôles.');
            return;
        }

        // Prevent super_admin from changing their own role
        if ($userToUpdate->id === $currentUser->id) {
            $this->dispatch('error', message: 'Vous ne pouvez pas modifier votre propre rôle.');
            return;
        }

        // Prevent changing other super_admin roles
        if ($userToUpdate->isSuperAdmin() && $this->newRole !== 'super_admin') {
            $this->dispatch('error', message: 'Vous ne pouvez pas modifier le rôle d\'un autre super administrateur.');
            return;
        }

        $userToUpdate->role = $this->newRole;
        $userToUpdate->save();

        logActivity('Modification du rôle d\'un utilisateur', [
            'user_id' => $userToUpdate->id,
            'user_name' => $userToUpdate->name,
            'old_role' => $userToUpdate->getOriginal('role'),
            'new_role' => $this->newRole,
        ], $userToUpdate);

        $this->dispatch('success', message: 'Rôle mis à jour avec succès.');

        $this->confirmingRoleChange = null;
        $this->newRole = '';
    }

    public function cancelConfirmation()
    {
        $this->confirmingStatusChange = null;
        $this->confirmingRoleChange = null;
        $this->newRole = '';
    }
}
