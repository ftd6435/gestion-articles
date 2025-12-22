<?php

namespace App\Livewire\Articles;

use App\Models\Articles\ArticleModel;
use App\Models\Category;
use App\Models\DeviseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Articles extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCategory = '';
    public $filterDevise = '';

    public $articles;
    public $categories;
    public $devises;

    public $articleId;
    public $reference;
    public $category_id;
    public $devise_id;
    public $designation;
    public $description;
    public $prix_achat;
    public $prix_vente;
    public $unite;
    public $status = true;
    public $showModal = false;

    protected function rules()
    {
        return [
            'reference' => ['required', 'string', 'min:3', Rule::unique('article_models', 'reference')->ignore($this->articleId)],
            'category_id' => ['required', 'exists:categories,id'],
            'devise_id' => ['required', 'exists:devise_models,id'],
            'designation' => 'required|string|min:3',
            'description' => 'nullable|string',
            'prix_achat' => 'nullable|numeric|min:0',
            'prix_vente' => 'nullable|numeric|min:0|required_with:prix_achat|gte:prix_achat',
            'unite' => 'nullable|string|min:2',
            'status' => 'boolean',
        ];
    }

    protected function messages()
    {
        return [
            // Référence / Code magasin
            'reference.required' => 'La référence de l’article est obligatoire.',
            'reference.string'   => 'La référence de l’article doit être une chaîne de caractères.',
            'reference.min'      => 'La référence de l’article doit contenir au moins :min caractères.',
            'reference.unique'   => 'Cette référence est déjà utilisée par un autre article.',

            // Catégorie
            'category_id.required' => 'Veuillez sélectionner une catégorie.',
            'category_id.exists'   => 'La catégorie sélectionnée est invalide.',

            // Devise
            'devise_id.required' => 'Veuillez sélectionner une devise.',
            'devise_id.exists'   => 'La devise sélectionnée est invalide.',

            // Désignation
            'designation.required' => 'La désignation de l’article est obligatoire.',
            'designation.string'   => 'La désignation doit être une chaîne de caractères.',
            'designation.min'      => 'La désignation doit contenir au moins :min caractères.',

            // Description
            'description.string' => 'La description doit être une chaîne de caractères.',

            // Prix d’achat
            'prix_achat.numeric' => 'Le prix d’achat doit être un nombre.',
            'prix_achat.min'     => 'Le prix d’achat ne peut pas être négatif.',

            // Prix de vente
            'prix_vente.numeric' => 'Le prix de vente doit être un nombre.',
            'prix_vente.min'     => 'Le prix de vente ne peut pas être négatif.',

            'prix_vente.gte' => 'Le prix de vente doit être supérieur ou égal au prix d’achat.',
            'prix_vente.gt'  => 'Le prix de vente doit être strictement supérieur au prix d’achat.',

            // Unité
            'unite.string' => 'L’unité doit être une chaîne de caractères.',
            'unite.min'    => 'L’unité doit contenir au moins :min caractères.',

            // Status
            'status.boolean' => 'Le statut de l’article est invalide.',
        ];
    }


    public function mount()
    {
        $this->loadArticles();
        $this->loadCategories();
        $this->loadDevises();
    }

    public function loadArticles()
    {
        $this->articles = ArticleModel::query()
            ->with(['category', 'devise'])

            // 🔍 Search (reference + designation)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('designation', 'like', '%' . $this->search . '%');
                });
            })

            // 📂 Category filter
            ->when($this->filterCategory, function ($query) {
                $query->where('category_id', $this->filterCategory);
            })

            // 💱 Devise filter
            ->when($this->filterDevise, function ($query) {
                $query->where('devise_id', $this->filterDevise);
            })

            ->latest()
            ->get();
    }

    public function updated($property)
    {
        if (in_array($property, [
            'search',
            'filterCategory',
            'filterDevise'
        ])) {
            $this->loadArticles();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterCategory = '';
        $this->filterDevise = '';

        $this->loadArticles();
    }


    public function loadCategories()
    {
        $this->categories = Category::with('createdBy', 'updatedBy', 'articles')->active()->latest()->get();
    }

    public function loadDevises()
    {
        $this->devises = DeviseModel::with('createdBy', 'updatedBy', 'articles')->active()->latest()->get();
    }

    public function resetForm()
    {
        $this->reset(['articleId', 'reference', 'category_id', 'devise_id', 'designation', 'description', 'prix_achat', 'prix_vente', 'unite']);
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
            $article = ArticleModel::findOrFail($id);

            $this->articleId = $article->id;
            $this->reference = $article->reference;
            $this->category_id = $article->category_id;
            $this->devise_id = $article->devise_id;
            $this->designation = $article->designation;
            $this->description = $article->description;
            $this->prix_achat = $article->prix_achat;
            $this->prix_vente = $article->prix_vente;
            $this->unite = $article->unite;
            $this->status = (bool) $article->status;

            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Magasin introuvable');
        }
    }

    public function store()
    {
        $this->validate();

        try {
            ArticleModel::updateOrCreate(
                ['id' => $this->articleId],
                [
                    'reference'    => $this->reference,
                    'category_id'  => $this->category_id,
                    'devise_id'  => $this->devise_id,
                    'designation'  => $this->designation,
                    'description'  => $this->description,
                    'prix_achat'   => $this->prix_achat,
                    'prix_vente'   => $this->prix_vente,
                    'unite'        => $this->unite,
                    'status'       => $this->status,
                    'updated_by'   => Auth::id(),
                    'created_by'   => $this->articleId ? null : Auth::id(),
                ]
            );

            $this->loadArticles();
            $this->closeModal();

            session()->flash(
                'success',
                $this->articleId
                    ? 'Article modifié avec succès'
                    : 'Article créé avec succès'
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
        $article = ArticleModel::findOrFail($id);
        $article->update([
            'status' => !$article->status,
            'updated_by' => Auth::id(),
        ]);

        $this->loadArticles();
        session()->flash('success', 'Statut modifié avec succès');
    }

    public function deleteConfirm($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function confirmDelete($id)
    {
        ArticleModel::findOrFail($id)->delete();
        $this->loadArticles();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.articles.articles', [
            'title' => 'Gestion des Articles',
            'breadcrumb' => 'Articles'
        ]);
    }
}
