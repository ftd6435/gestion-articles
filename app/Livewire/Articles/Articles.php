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

    // Filters
    public $search = '';
    public $filterCategory = '';
    public $filterDevise = '';
    public $filterStatus = '';

    public $filterStockLevel = '';
    public $filterMargin = '';
    public $filterLastUpdated = '';
    public $showAdvancedFilters = false;
    public $perPage = 15; // Items per page

    // Data
    public $categories;
    public $devises;

    // Statistics (calculated on all articles, not just paginated ones)
    public $activeCount = 0;
    public $inactiveCount = 0;
    public $avgPurchasePrice = 0;
    public $avgSalePrice = 0;
    public $minPurchasePrice = 0;
    public $maxPurchasePrice = 0;
    public $avgMargin = 0;
    public $recentSalesCount = 0;
    public $topCategory = '';

    // Form properties
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

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCategory' => ['except' => ''],
        'filterDevise' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->loadCategories();
        $this->loadDevises();
        $this->calculateStatistics();
    }

    // Build the query for pagination
    protected function getQuery()
    {
        $query = ArticleModel::query()
            ->with([
                'category',
                'devise',
                'ligneCommandes',
                'ligneReceptions',
                'ligneVentes'
            ]);

        // Search
        $query->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhere('designation', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        });

        // Category filter
        $query->when($this->filterCategory, function ($query) {
            $query->where('category_id', $this->filterCategory);
        });

        // Devise filter
        $query->when($this->filterDevise, function ($query) {
            $query->where('devise_id', $this->filterDevise);
        });

        // Status filter
        $query->when($this->filterStatus, function ($query) {
            if ($this->filterStatus === 'active') {
                $query->where('status', true);
            } elseif ($this->filterStatus === 'inactive') {
                $query->where('status', false);
            }
        });

        // Last updated filter
        $query->when($this->filterLastUpdated, function ($query) {
            $now = now();
            if ($this->filterLastUpdated === 'today') {
                $query->whereDate('updated_at', $now->toDateString());
            } elseif ($this->filterLastUpdated === 'week') {
                $query->whereBetween('updated_at', [$now->startOfWeek(), $now->endOfWeek()]);
            } elseif ($this->filterLastUpdated === 'month') {
                $query->whereMonth('updated_at', $now->month)
                    ->whereYear('updated_at', $now->year);
            } elseif ($this->filterLastUpdated === 'year') {
                $query->whereYear('updated_at', $now->year);
            }
        });

        // Stock level filter (you'll need to implement this differently)
        // This is complex as it requires calculating stock for each article
        $query->when($this->filterStockLevel, function ($query) {
            // This would need a subquery or separate calculation
            // For now, we'll leave it as is
        });

        // Margin filter
        $query->when($this->filterMargin, function ($query) {
            // This requires calculating margin for each article
            // Implementation depends on your database structure
        });

        return $query->orderBy('created_at', 'desc');
    }

    public function calculateStatistics()
    {
        // For statistics, we need ALL articles, not just paginated ones
        $allArticles = ArticleModel::with('ligneVentes')->get();

        $this->activeCount = $allArticles->where('status', true)->count();
        $this->inactiveCount = $allArticles->where('status', false)->count();

        $prices = $allArticles->pluck('prix_achat')->filter();
        $this->avgPurchasePrice = $prices->avg() ?? 0;
        $this->minPurchasePrice = $prices->min() ?? 0;
        $this->maxPurchasePrice = $prices->max() ?? 0;

        $salePrices = $allArticles->pluck('prix_vente')->filter();
        $this->avgSalePrice = $salePrices->avg() ?? 0;

        // Calculate average margin
        $margins = $allArticles->filter(function ($article) {
            return $article->prix_achat > 0 && $article->prix_vente > 0;
        })->map(function ($article) {
            return (($article->prix_vente - $article->prix_achat) / $article->prix_achat) * 100;
        });
        $this->avgMargin = $margins->avg() ?? 0;

        // Recent sales (last 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        $this->recentSalesCount = $allArticles->sum(function ($article) use ($thirtyDaysAgo) {
            return $article->ligneVentes->where('created_at', '>=', $thirtyDaysAgo)->sum('quantity');
        });

        // Top category
        $categoryCounts = $allArticles->groupBy('category_id')->map->count();
        if ($categoryCounts->isNotEmpty()) {
            $topCategoryId = $categoryCounts->sortDesc()->keys()->first();
            $topCategory = Category::find($topCategoryId);
            $this->topCategory = $topCategory ? $topCategory->name : '—';
        }
    }

    public function updated($property)
    {
        // Reset page when filters change (except for pagination properties)
        if (in_array($property, [
            'search',
            'filterCategory',
            'filterDevise',
            'filterStatus',
            'filterStockLevel',
            'filterMargin',
            'filterLastUpdated'
        ])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterCategory = '';
        $this->filterDevise = '';
        $this->filterStatus = '';
        $this->filterStockLevel = '';
        $this->filterMargin = '';
        $this->filterLastUpdated = '';
        $this->showAdvancedFilters = false;
        $this->resetPage();

        $this->calculateStatistics();
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function showArticleDetails($id)
    {
        // Dispatch event to show article details modal
        $this->dispatch('show-article-details', articleId: $id);
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
        $this->reset([
            'articleId',
            'reference',
            'category_id',
            'devise_id',
            'designation',
            'description',
            'prix_achat',
            'prix_vente',
            'unite'
        ]);
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
            session()->flash('error', 'Article introuvable');
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

            $this->dispatch('article-saved');
            $this->closeModal();

            // Recalculate statistics
            $this->calculateStatistics();

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

        // Recalculate statistics
        $this->calculateStatistics();

        session()->flash('success', 'Statut modifié avec succès');
    }

    public function deleteConfirm($id)
    {
        $article = ArticleModel::find($id);

        $this->dispatch(
            'confirm-delete',
            id: $id,
            itemName: $article ? $article->reference . ' ' . $article->designation : 'cet article'
        );
    }

    public function confirmDelete($id)
    {
        try {
            $article = ArticleModel::findOrFail($id);
            $name = $article->reference . ' ' . $article->designation;
            $article->delete();

            // Recalculate statistics
            $this->calculateStatistics();

            $this->dispatch(
                'delete-success',
                message: "L'article \"{$name}\" a été supprimé avec succès."
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'delete-error',
                message: 'Erreur lors de la suppression : ' . $e->getMessage()
            );
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // In your Articles component class
    public function render()
    {
        // Get paginated articles
        $articles = $this->getQuery()->paginate($this->perPage); // Changed variable name

        view()->share('title', "Gestion des Articles");
        view()->share('breadcrumb', "Articles");

        return view('livewire.articles.articles', [
            'articles' => $articles,
        ]);
    }
}
