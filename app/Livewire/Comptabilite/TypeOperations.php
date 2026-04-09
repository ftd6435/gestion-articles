<?php

namespace App\Livewire\Comptabilite;

use App\Models\Comptabilite\TypeOperation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TypeOperations extends Component
{
    public $types;

    public $typeId;
    public $name = '';
    public $nature = 0;
    public $description;
    public $showModal = false;

    protected $listeners = ['confirmDelete'];

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
                Rule::unique('type_operations', 'name')->ignore($this->typeId),
            ],
            'nature' => ['required', Rule::in([0, 1])],
            'description' => 'nullable|string|max:255',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.unique' => 'Ce type d\'opération existe déjà.',
            'nature.required' => 'La nature est obligatoire.',
            'nature.in' => 'La nature est invalide.',
        ];
    }

    public function mount()
    {
        $this->loadTypes();
    }

    public function loadTypes(): void
    {
        $this->types = TypeOperation::query()
            ->orderBy('nature')
            ->orderBy('name')
            ->get();
    }

    public function resetForm(): void
    {
        $this->reset(['typeId', 'name', 'nature', 'description']);
        $this->nature = 0;
        $this->resetValidation();
    }

    public function create(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('comptabilite.types-operations', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer des types d\'opérations.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('comptabilite.types-operations', 'update')) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier des types d\'opérations.');
            return;
        }

        $type = TypeOperation::findOrFail($id);
        $this->typeId = $type->id;
        $this->name = $type->name;
        $this->nature = (int) $type->nature;
        $this->description = $type->description;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function store(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($this->typeId) {
            if (!$currentUser?->canAccess('comptabilite.types-operations', 'update')) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier des types d\'opérations.');
                return;
            }
        } else {
            if (!$currentUser?->canAccess('comptabilite.types-operations', 'create')) {
                session()->flash('error', 'Vous n\'avez pas la permission de créer des types d\'opérations.');
                return;
            }
        }

        $this->validate();

        if ($this->typeId) {
            $type = TypeOperation::findOrFail($this->typeId);
            $type->update([
                'name' => $this->name,
                'nature' => (int) $this->nature,
                'description' => $this->description,
            ]);
            $message = 'Type d\'opération modifié avec succès.';
        } else {
            TypeOperation::create([
                'name' => $this->name,
                'nature' => (int) $this->nature,
                'description' => $this->description,
            ]);
            $message = 'Type d\'opération créé avec succès.';
        }

        $this->loadTypes();
        $this->closeModal();
        session()->flash('success', $message);
    }

    public function deleteConfirm($id): void
    {
        $type = TypeOperation::findOrFail($id);

        $this->dispatch(
            'confirm-delete',
            id: $type->id,
            itemName: $type->name
        );
    }

    public function confirmDelete($id): void
    {
        try {
            $type = TypeOperation::findOrFail($id);

            if ($type->operations()->exists()) {
                $this->dispatch(
                    'delete-error',
                    message: 'Impossible de supprimer ce type: il contient déjà des opérations.'
                );
                return;
            }

            $name = $type->name;
            $type->delete();
            $this->loadTypes();

            $this->dispatch(
                'delete-success',
                message: "Le type \"{$name}\" a été supprimé avec succès."
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
        view()->share('title', 'Types d\'opérations');
        view()->share('breadcrumb', 'Comptabilité / Types d\'opérations');

        return view('livewire.comptabilite.type-operations');
    }
}
