<?php

namespace App\Livewire\Configuration;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanySettings extends Component
{
    use WithFileUploads;

    public $settingId;
    public $name = '';
    public $short_name;
    public $telephone;
    public $email;
    public $adresse;
    public $logo;
    public $logoPreview;
    public $existingLogoPath;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:2|max:150',
            'short_name' => 'nullable|string|min:2|max:60',
            'telephone' => 'nullable|string|min:6|max:30',
            'email' => 'nullable|email|max:150',
            'adresse' => 'nullable|string|max:150',
            'logo' => 'nullable|image|max:2048',
        ];
    }

    public function mount(): void
    {
        $setting = CompanySetting::query()->orderBy('id')->first();

        if ($setting) {
            $this->settingId = $setting->id;
            $this->name = $setting->name;
            $this->short_name = $setting->short_name;
            $this->telephone = $setting->telephone;
            $this->email = $setting->email;
            $this->adresse = $setting->adresse;
            $this->existingLogoPath = $setting->logo_path;
            $this->logoPreview = $setting->logo_path ? asset($setting->logo_path) : null;
        } else {
            $this->logoPreview = null;
            $this->existingLogoPath = null;
        }
    }

    public function updatedLogo(): void
    {
        $this->validateOnly('logo');
        $this->logoPreview = $this->logo ? $this->logo->temporaryUrl() : $this->logoPreview;
    }

    public function removeLogo(): void
    {
        $this->logo = null;
        $this->logoPreview = $this->existingLogoPath ? asset($this->existingLogoPath) : null;
    }

    public function save(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($this->settingId) {
            if (!$currentUser?->canAccess('configuration.settings', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier les paramètres entreprise.');
                return;
            }
        } else {
            if (!$currentUser?->canAccess('configuration.settings', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer les paramètres entreprise.');
                return;
            }
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'short_name' => $this->short_name,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'adresse' => $this->adresse,
        ];

        if ($this->logo) {
            if ($this->existingLogoPath && str_starts_with($this->existingLogoPath, 'storage/')) {
                $relativePath = str_replace('storage/', '', $this->existingLogoPath);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }

            $path = $this->logo->store('company', 'public');
            $data['logo_path'] = 'storage/' . $path;
        }

        if ($this->settingId) {
            $setting = CompanySetting::findOrFail($this->settingId);
            $setting->update($data + ['updated_by' => Auth::id()]);
        } else {
            $setting = CompanySetting::create($data + ['created_by' => Auth::id()]);
            $this->settingId = $setting->id;
        }

        $setting->refresh();
        $this->existingLogoPath = $setting->logo_path;
        $this->logoPreview = $setting->logo_path ? asset($setting->logo_path) : null;
        $this->logo = null;

        session()->flash('success', 'Paramètres entreprise mis à jour avec succès.');
    }

    public function render()
    {
        view()->share('title', "Paramètres entreprise");
        view()->share('breadcrumb', "Configuration / Entreprise");

        return view('livewire.configuration.company-settings');
    }
}
