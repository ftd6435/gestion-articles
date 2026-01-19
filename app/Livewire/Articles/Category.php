<?php

namespace App\Livewire\Articles;

use App\Models\Category as CategoryModel;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Category extends Component
{
    public $categories;
    public $breadcrumb;
    public $title;

    public $categoryId;
    public $name;
    public $description;
    public $status = true;
    public $showCategoryModal = false;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3', Rule::unique('categories', 'name')->ignore($this->categoryId)],
            'description' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'name.unique' => 'Cette catégorie existe déjà',
            'name.string' => 'Le nom est une chaine de caractère',
            'name.min' => 'Le nom doit contenir au moins 3 caractères',
            'description.required' => 'La description est une chaine de caractère',
        ];
    }

    protected $listeners = ['confirmDelete'];

    public function mount()
    {
        $this->breadcrumb = "Catégories";
        $this->title = "Catégories Des Articles";
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = CategoryModel::with('createdBy', 'updatedBy')->latest()->get();
    }

    public function resetForm()
    {
        $this->reset(['categoryId', 'name', 'description']);
        $this->status = true;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetForm();
    }

    public function edit($id)
    {
        try {
            $category = CategoryModel::findOrFail($id);

            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->status = (bool) $category->status;

            $this->showCategoryModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Catégorie introuvable');
        }
    }

    public function storeCategory()
    {
        $this->validate();

        try {
            if ($this->categoryId) {
                // Update existing
                $category = CategoryModel::findOrFail($this->categoryId);
                $category->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'status' => $this->status,
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Catégorie modifiée avec succès';

                logActivity('Modification d\'une catégorie', [
                    'old' => [
                        'name' => $category->name,
                        'description' => $category->description,
                        'status' => $category->status,
                    ],
                    'new' => [
                        'name' => $this->name,
                        'description' => $this->description,
                        'status' => $this->status,
                    ]
                ], $category);
            } else {
                // Create new
                $category = CategoryModel::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                ]);
                $message = 'Catégorie créée avec succès';

                logActivity('Création d\'une catégorie', [
                    'name' => $this->name,
                    'description' => $this->description,
                    'status' => $this->status,
                ], $category);
            }

            $this->loadCategories();
            $this->closeCategoryModal();

            session()->flash('success', $message);
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $category = CategoryModel::findOrFail($id);
        $category->update([
            'status' => !$category->status,
            'updated_by' => Auth::id(),
        ]);

        $this->loadCategories();
        session()->flash('success', 'Statut modifié avec succès');
    }

    public function deleteConfirm($id)
    {
        $category = CategoryModel::find($id);

        logActivity('Demande de suppression d\'une catégorie', [
            'name' => $category->name,
            'description' => $category->description,
            'status' => $category->status,
        ], $category);

        $this->dispatch(
            'confirm-delete',
            id: $id,
            itemName: $category ? $category->name : 'cette catégorie'
        );
    }

    public function confirmDelete($id)
    {
        try {
            $category = CategoryModel::findOrFail($id);
            $name = $category->name;

            logActivity('Suppression confirmée d\'une catégorie', [
                'name' => $category->name,
                'description' => $category->description,
                'status' => $category->status,
            ], $category);

            $category->delete();

            $this->loadCategories();

            $this->dispatch(
                'delete-success',
                message: "La catégorie \"{$name}\" a été supprimée avec succès."
            );
        } catch (Exception $e) {
            $this->dispatch(
                'delete-error',
                message: 'Erreur lors de la suppression : ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        view()->share('title', $this->title);
        view()->share('breadcrumb', $this->breadcrumb);

        return view('livewire.articles.category');
    }
}
