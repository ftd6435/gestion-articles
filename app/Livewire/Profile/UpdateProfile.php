<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;

class UpdateProfile extends Component
{
    use WithFileUploads;

    public $user;
    public $name;
    public $email;
    public $telephone;
    public $image;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public $imagePreview;
    public $passwordUpdateCount = 0;
    public $remainingUpdates = 3;
    public $lastPasswordReset;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,',
        'telephone' => 'required|string|max:20',
        'image' => 'nullable|image|max:2048',
        'current_password' => 'nullable|required_with:new_password',
        'new_password' => 'nullable|min:8|confirmed',
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->telephone = $this->user->telephone;

        $this->imagePreview = $this->getImageUrl($this->user->image);
        $this->checkPasswordUpdateLimit();
    }

    // Méthode helper pour obtenir l'URL de l'image
    private function getImageUrl($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        // Si le chemin commence par storage/
        if (strpos($imagePath, 'storage/') === 0) {
            return asset($imagePath);
        }

        // Sinon, essayer Storage::url
        try {
            return Storage::url($imagePath);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function checkPasswordUpdateLimit()
    {
        // Récupérer et décoder les mises à jour de mot de passe
        $passwordUpdates = $this->user->password_updates;

        // Si c'est une chaîne JSON, décoder
        if (is_string($passwordUpdates)) {
            $passwordUpdates = json_decode($passwordUpdates, true) ?? [];
        }

        // Si c'est null, initialiser comme tableau vide
        if (is_null($passwordUpdates)) {
            $passwordUpdates = [];
        }

        $currentMonth = Carbon::now()->format('Y-m');

        // Filtrer les mises à jour du mois courant
        $monthlyUpdates = array_filter($passwordUpdates, function ($date) use ($currentMonth) {
            if (is_string($date)) {
                return Carbon::parse($date)->format('Y-m') === $currentMonth;
            }
            return false;
        });

        $this->passwordUpdateCount = count($monthlyUpdates);
        $this->remainingUpdates = max(0, 3 - $this->passwordUpdateCount);

        if (!empty($monthlyUpdates)) {
            $lastUpdate = end($monthlyUpdates);
            $this->lastPasswordReset = Carbon::parse($lastUpdate)->locale('fr')->diffForHumans();
        }
    }

    public function updatedImage()
    {
        $this->validateOnly('image');
        $this->imagePreview = $this->image->temporaryUrl();
    }

    public function removeImage()
    {
        $this->image = null;
        $this->imagePreview = $this->getImageUrl($this->user->image);
    }

    public function updateProfile()
    {
        // Règle d'unicité de l'email avec ID utilisateur
        $this->rules['email'] = 'required|string|email|max:255|unique:users,email,' . $this->user->id;

        $this->validate();

        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'telephone' => $this->telephone,
        ];

        // Gestion de l'image
        if ($this->image) {
            // Supprimer l'ancienne image si elle existe
            if ($this->user->image) {
                // Nettoyer le chemin
                $oldImagePath = $this->user->image;

                // Si c'est un chemin storage/, extraire le chemin relatif
                if (strpos($oldImagePath, 'storage/') === 0) {
                    $relativePath = str_replace('storage/', '', $oldImagePath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // Stocker la nouvelle image
            $path = $this->image->store('profile-images', 'public');

            // Stocker le chemin relatif (storage/path/to/image.jpg)
            $updateData['image'] = 'storage/' . $path;
        }

        // Gestion du mot de passe
        if ($this->new_password) {
            if ($this->remainingUpdates <= 0) {
                $this->addError('new_password', 'Vous ne pouvez modifier votre mot de passe que 3 fois par mois.');
                return;
            }

            if (!Hash::check($this->current_password, $this->user->password)) {
                $this->addError('current_password', 'Le mot de passe actuel est incorrect.');
                return;
            }

            $updateData['password'] = Hash::make($this->new_password);

            // Récupérer et préparer les mises à jour existantes
            $passwordUpdates = $this->user->password_updates;

            // Décoder si c'est une chaîne JSON
            if (is_string($passwordUpdates)) {
                $passwordUpdates = json_decode($passwordUpdates, true) ?? [];
            }

            // S'assurer que c'est un tableau
            if (!is_array($passwordUpdates)) {
                $passwordUpdates = [];
            }

            // Ajouter la date actuelle
            $passwordUpdates[] = now()->toDateTimeString();

            // Stocker comme JSON
            $updateData['password_updates'] = json_encode($passwordUpdates);

            // Réinitialiser les champs de mot de passe
            $this->current_password = '';
            $this->new_password = '';
            $this->new_password_confirmation = '';
        }

        // Mettre à jour l'utilisateur
        $this->user->update($updateData);

        // Rafraîchir l'utilisateur pour obtenir les nouvelles données
        $this->user->refresh();

        // Mettre à jour l'aperçu de l'image si une nouvelle image a été téléchargée
        if ($this->image) {
            $this->imagePreview = asset($updateData['image']);
            $this->image = null;
        }

        // Recalculer les limites de mot de passe
        $this->checkPasswordUpdateLimit();

        // Message de succès
        session()->flash('success', 'Profil mis à jour avec succès !');
    }

    public function render()
    {
        view()->share('title', "Profil utilisateur");
        view()->share('breadcrumb', "Profil");

        return view('livewire.profile.update-profile');
    }
}
