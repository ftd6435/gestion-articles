<?php

namespace App\Livewire\Articles;

use App\Models\Category as CategoryModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Category extends Component
{
    public $categories;

    public $categoryId;
    public $name;
    public $description;
    public $status = true;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|string|min:3',
        'description' => 'nullable|string',
        'status' => 'boolean',
    ];

    protected $listeners = ['confirmDelete'];

    public function mount()
    {
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
            $category = CategoryModel::findOrFail($id);

            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->status = (bool) $category->status;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Catégorie introuvable');
        }
    }

    public function store()
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
            } else {
                // Create new
                CategoryModel::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
                $message = 'Catégorie créée avec succès';
            }

            $this->dispatch('close-category-modal');
            $this->loadCategories();
            $this->closeModal();

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
        $this->dispatch('confirm-delete', id: $id);
    }

    public function confirmDelete($id)
    {
        CategoryModel::findOrFail($id)->delete();
        $this->loadCategories();
        session()->flash('success', 'Catégorie supprimée avec succès');
    }

    public function render()
    {
        return view('livewire.articles.category', [
            'title' => 'Catégories Des Articles',
            'breadcrumb' => 'Catégories'
        ]);
    }
}
