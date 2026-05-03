<?php

namespace App\Livewire\Stock;

use App\Models\Articles\ArticleModel;
use App\Models\Stock\StockInitialArticle;
use App\Models\Warehouse\EtagereModel;
use App\Models\Warehouse\MagasinModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockInitial extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Form fields
    public $stockId;
    public $article_id;
    public $magasin_id;
    public $etagere_id;
    public $quantity = 0;
    public $date_expiration;
    public $date_inventaire;
    public $notes;

    // Modal control
    public $showModal = false;
    public $isEditMode = false;

    // Search and filter
    public $search = '';
    public $filterMagasin = '';
    public $filterEtagere = '';
    public $filterExpirable = '';

    // Article search
    public $articleSearch = '';
    public $showArticleDropdown = false;
    public $filteredArticles = [];
    public $selectedArticleDesignation = '';

    // Data for dropdowns
    public $articles = [];
    public $magasins = [];
    public $etageres = [];
    public $etageresList = [];

    protected function rules()
    {
        return [
            'article_id' => 'required|exists:article_models,id',
            'magasin_id' => 'required|exists:magasin_models,id',
            'etagere_id' => 'required|exists:etagere_models,id',
            'quantity' => 'required|numeric|min:0',
            'date_expiration' => 'nullable|date|after:today',
            'date_inventaire' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ];
    }

    protected function messages()
    {
        return [
            'article_id.required' => 'L\'article est obligatoire',
            'article_id.exists' => 'L\'article sélectionné n\'existe pas',
            'magasin_id.required' => 'Le magasin est obligatoire',
            'magasin_id.exists' => 'Le magasin sélectionné n\'existe pas',
            'etagere_id.required' => 'L\'étagère est obligatoire',
            'etagere_id.exists' => 'L\'étagère sélectionnée n\'existe pas',
            'quantity.required' => 'La quantité est obligatoire',
            'quantity.integer' => 'La quantité doit être un nombre entier',
            'quantity.min' => 'La quantité doit être positive',
            'date_expiration.date' => 'La date d\'expiration doit être une date valide',
            'date_expiration.after' => 'La date d\'expiration doit être dans le futur',
            'date_inventaire.required' => 'La date d\'inventaire est obligatoire',
            'date_inventaire.date' => 'La date d\'inventaire doit être une date valide',
            'date_inventaire.before_or_equal' => 'La date d\'inventaire ne peut pas être dans le futur',
            'notes.max' => 'Les notes ne peuvent pas dépasser 500 caractères',
        ];
    }

    public function mount()
    {
        $this->loadDropdowns();
        $this->date_inventaire = now()->format('Y-m-d');

        // Auto-select default warehouse and shelf
        $defaultMagasin = MagasinModel::active()->where('is_default', true)->first();
        if ($defaultMagasin) {
            $this->magasin_id = $defaultMagasin->id;
            $this->updatedMagasinId($this->magasin_id);

            // Auto-select default shelf
            $defaultEtagere = EtagereModel::active()
                ->where('magasin_id', $defaultMagasin->id)
                ->where('is_default', true)
                ->first();
            if ($defaultEtagere) {
                $this->etagere_id = $defaultEtagere->id;
            }
        }
    }

    public function loadDropdowns()
    {
        $articles = ArticleModel::active()
            ->with('category', 'devise')
            ->orderBy('reference')
            ->get();

        // Convert to array for Livewire serialization
        $this->articles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'reference' => $article->reference,
                'designation' => $article->designation,
            ];
        })->toArray();

        $this->filteredArticles = array_slice($this->articles, 0, 10);

        $this->magasins = MagasinModel::active()
            ->orderBy('nom')
            ->get();

        $this->etageresList = EtagereModel::active()
            ->with('magasin')
            ->orderBy('code_etagere')
            ->get();
    }

    public function updatedMagasinId($value)
    {
        if ($value) {
            $this->etageres = EtagereModel::active()
                ->where('magasin_id', $value)
                ->orderBy('code_etagere')
                ->get();

            // Reset etagere_id if it doesn't belong to selected magasin
            if ($this->etagere_id) {
                $etagereExists = false;
                foreach ($this->etageres as $etagere) {
                    if ($etagere->id == $this->etagere_id) {
                        $etagereExists = true;
                        break;
                    }
                }
                if (!$etagereExists) {
                    $this->etagere_id = null;
                }
            }
        } else {
            $this->etageres = [];
            $this->etagere_id = null;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterMagasin()
    {
        $this->resetPage();
    }

    public function updatedFilterEtagere()
    {
        $this->resetPage();
    }

    public function updatedFilterExpirable()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['stockId', 'article_id', 'magasin_id', 'etagere_id', 'quantity', 'date_expiration', 'notes', 'articleSearch', 'selectedArticleDesignation']);
        $this->date_inventaire = now()->format('Y-m-d');
        $this->etageres = [];
        $this->isEditMode = false;
        $this->showArticleDropdown = false;
        $this->resetValidation();

        // Auto-select defaults again
        $defaultMagasin = MagasinModel::active()->where('is_default', true)->first();
        if ($defaultMagasin) {
            $this->magasin_id = $defaultMagasin->id;
            $this->updatedMagasinId($this->magasin_id);

            $defaultEtagere = EtagereModel::active()
                ->where('magasin_id', $defaultMagasin->id)
                ->where('is_default', true)
                ->first();
            if ($defaultEtagere) {
                $this->etagere_id = $defaultEtagere->id;
            }
        }
    }

    /* ================ SEARCHABLE ARTICLE SELECT ====================*/

    public function updatedArticleSearch(): void
    {
        if (empty($this->articleSearch)) {
            $this->filteredArticles = array_slice($this->articles, 0, 10);
            $this->showArticleDropdown = true;
            return;
        }

        $search = strtolower($this->articleSearch);
        $filtered = array_filter($this->articles, function ($article) use ($search) {
            return str_contains(strtolower($article['designation']), $search) ||
                str_contains(strtolower($article['reference']), $search);
        });

        $this->filteredArticles = array_slice($filtered, 0, 10);
        $this->showArticleDropdown = true;
    }

    public function selectArticle($articleId): void
    {
        $article = collect($this->articles)->firstWhere('id', $articleId);
        if ($article) {
            $this->article_id = $article['id'];
            $this->selectedArticleDesignation = $article['designation'];
            $this->articleSearch = $article['designation'] . ' (' . $article['reference'] . ')';
            $this->showArticleDropdown = false;
        }
    }

    public function clearArticle(): void
    {
        $this->article_id = null;
        $this->selectedArticleDesignation = '';
        $this->articleSearch = '';
        $this->showArticleDropdown = false;
    }

    public function closeArticleDropdown(): void
    {
        $this->dispatch('article-dropdown-close-delay');
    }

    public function create()
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('stock.initial', 'create')) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer des stocks initiaux.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('stock.initial', 'update')) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier des stocks initiaux.');
            return;
        }

        try {
            $stock = StockInitialArticle::with('article', 'magasin', 'etagere')->findOrFail($id);

            $this->stockId = $stock->id;
            $this->article_id = $stock->article_id;
            $this->magasin_id = $stock->magasin_id;

            // Set article search
            $this->selectedArticleDesignation = $stock->article->designation;
            $this->articleSearch = $stock->article->designation . ' (' . $stock->article->reference . ')';

            // Load etageres for selected magasin
            $this->updatedMagasinId($this->magasin_id);

            $this->etagere_id = $stock->etagere_id;
            $this->quantity = $stock->quantity;
            $this->date_expiration = $stock->date_expiration?->format('Y-m-d');
            $this->date_inventaire = $stock->date_inventaire->format('Y-m-d');
            $this->notes = $stock->notes;

            $this->isEditMode = true;
            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Stock initial introuvable.');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'article_id' => $this->article_id,
                'magasin_id' => $this->magasin_id,
                'etagere_id' => $this->etagere_id,
                'quantity' => $this->quantity,
                'date_expiration' => $this->date_expiration ?: null,
                'date_inventaire' => $this->date_inventaire,
                'notes' => $this->notes,
            ];

            if ($this->isEditMode && $this->stockId) {
                // Update existing
                /** @var \App\Models\User|null $currentUser */
                $currentUser = Auth::user();
                if (!$currentUser?->canAccess('stock.initial', 'update')) {
                    session()->flash('error', 'Vous n\'avez pas la permission de modifier des stocks initiaux.');
                    return;
                }

                $stock = StockInitialArticle::findOrFail($this->stockId);
                $data['updated_by'] = Auth::id();
                $stock->update($data);

                logActivity('Modification stock initial', [
                    'article' => $stock->article->reference,
                    'magasin' => $stock->magasin->nom,
                    'etagere' => $stock->etagere->code_etagere,
                    'quantity' => $this->quantity,
                ], $stock);

                session()->flash('success', 'Stock initial modifié avec succès.');
            } else {
                // Create new
                /** @var \App\Models\User|null $currentUser */
                $currentUser = Auth::user();
                if (!$currentUser?->canAccess('stock.initial', 'create')) {
                    session()->flash('error', 'Vous n\'avez pas la permission de créer des stocks initiaux.');
                    return;
                }

                // Check for duplicate (article + etagere)
                $exists = StockInitialArticle::where('article_id', $this->article_id)
                    ->where('etagere_id', $this->etagere_id)
                    ->exists();

                if ($exists) {
                    $this->addError('article_id', 'Un stock initial existe déjà pour cet article sur cette étagère.');
                    DB::rollBack();
                    return;
                }

                $data['created_by'] = Auth::id();
                $stock = StockInitialArticle::create($data);

                logActivity('Création stock initial', [
                    'article' => $stock->article->reference,
                    'magasin' => $stock->magasin->nom,
                    'etagere' => $stock->etagere->code_etagere,
                    'quantity' => $this->quantity,
                ], $stock);

                session()->flash('success', 'Stock initial créé avec succès.');
            }

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $stocks = StockInitialArticle::with([
            'article.category',
            'article.devise',
            'magasin',
            'etagere',
            'createdBy',
            'updatedBy'
        ])
            ->when($this->search, function ($query) {
                $query->whereHas('article', function ($q) {
                    $q->where('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('designation', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterMagasin, function ($query) {
                $query->where('magasin_id', $this->filterMagasin);
            })
            ->when($this->filterEtagere, function ($query) {
                $query->where('etagere_id', $this->filterEtagere);
            })
            ->when($this->filterExpirable !== '', function ($query) {
                if ($this->filterExpirable === '1') {
                    $query->whereNotNull('date_expiration');
                } else {
                    $query->whereNull('date_expiration');
                }
            })
            ->latest()
            ->paginate(15);

        return view('livewire.stock.stock-initial', [
            'stocks' => $stocks,
        ]);
    }
}
