<?php

namespace App\Livewire\Stock;

use App\Models\Articles\ArticleModel;
use App\Models\Category;
use App\Models\DeviseModel;
use App\Models\FournisseurModel;
use App\Models\Stock\CommandeFournisseur;
use App\Models\Stock\LigneCommandeFournisseur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateCommande extends Component
{
    /* ============================================================
     |  COMMANDE
     * ============================================================ */
    public $reference;
    public $fournisseur_id;
    public $devise_id;
    public $taux_change = 1;
    public $remise = 0;
    public $date_commande;
    public $status = 'EN_COURS';

    /* ============================================================
     |  LIGNE
     * ============================================================ */
    public $article_id;
    public $quantity;
    public $unit_price;
    public $lignes = [];

    /* ============================================================
     |  ARTICLE SEARCH
     * ============================================================ */
    public $articleSearch = '';
    public $articles;
    public $availableArticles;
    public $filteredArticles;

    /* ============================================================
     |  QUICK ARTICLE CREATION
     * ============================================================ */
    public $showModal = false;
    public $categories;

    public $newArticle = [
        'reference' => '',
        'category_id' => '',
        'designation' => '',
        'description' => '',
        'prix_achat' => '',
        'prix_vente' => '',
        'unite' => '',
        'status' => true,
    ];

    /* ============================================================
     |  LOOKUPS
     * ============================================================ */
    public $fournisseurs;
    public $devises;

    /* ============================================================
     |  VALIDATION
     * ============================================================ */
    protected function rules()
    {
        return [
            'reference' => 'required|unique:commande_fournisseurs,reference',
            'fournisseur_id' => 'required|exists:fournisseur_models,id',
            'devise_id' => 'required|exists:devise_models,id',
            'date_commande' => 'required|date',
            'remise' => 'nullable|numeric|min:0|max:100',
            'status' => 'required',
        ];
    }

    protected $articleRules = [
        'newArticle.reference' => 'required|min:3|unique:article_models,reference',
        'newArticle.category_id' => 'required|exists:categories,id',
        'newArticle.designation' => 'required|min:3',
        'devise_id' => 'required|exists:devise_models,id',
        'newArticle.prix_achat' => 'nullable|numeric|min:0',
        'newArticle.prix_vente' => 'nullable|numeric|min:0|gte:newArticle.prix_achat',
    ];

    /* ============================================================
 |  VALIDATION MESSAGES (French)
 * ============================================================ */
    protected function messages()
    {
        return [
            // Commande rules
            'reference.required' => 'La référence de la commande est obligatoire.',
            'reference.unique' => 'Cette référence de commande existe déjà.',

            'fournisseur_id.required' => 'Veuillez sélectionner un fournisseur.',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné est invalide.',

            'devise_id.required' => 'Veuillez sélectionner une devise.',
            'devise_id.exists' => 'La devise sélectionnée est invalide.',

            'date_commande.required' => 'La date de commande est obligatoire.',
            'date_commande.date' => 'La date de commande doit être une date valide.',

            'remise.numeric' => 'La remise doit être un nombre.',
            'remise.min' => 'La remise ne peut pas être négative.',
            'remise.max' => 'La remise ne peut pas dépasser 100%.',

            'status.required' => 'Le statut de la commande est obligatoire.',

            // Article rules
            'newArticle.reference.required' => 'La référence de l\'article est obligatoire.',
            'newArticle.reference.min' => 'La référence doit contenir au moins :min caractères.',
            'newArticle.reference.unique' => 'Cette référence d\'article existe déjà.',

            'newArticle.category_id.required' => 'Veuillez sélectionner une catégorie.',
            'newArticle.category_id.exists' => 'La catégorie sélectionnée est invalide.',

            'newArticle.designation.required' => 'La désignation de l\'article est obligatoire.',
            'newArticle.designation.min' => 'La désignation doit contenir au moins :min caractères.',

            'devise_id.required' => 'Veuillez sélectionner une devise pour l\'article.',
            'devise_id.exists' => 'La devise sélectionnée est invalide.',

            'newArticle.prix_achat.numeric' => 'Le prix d\'achat doit être un nombre.',
            'newArticle.prix_achat.min' => 'Le prix d\'achat ne peut pas être négatif.',

            'newArticle.prix_vente.numeric' => 'Le prix de vente doit être un nombre.',
            'newArticle.prix_vente.min' => 'Le prix de vente ne peut pas être négatif.',
            'newArticle.prix_vente.gte' => 'Le prix de vente doit être supérieur ou égal au prix d\'achat.',

            // Ligne commande validation
            'article_id.required' => 'Veuillez sélectionner un article.',
            'article_id.exists' => 'L\'article sélectionné est invalide.',

            'quantity.required' => 'La quantité est obligatoire.',
            'quantity.numeric' => 'La quantité doit être un nombre.',
            'quantity.min' => 'La quantité doit être au moins :min.',

            'unit_price.required' => 'Le prix unitaire est obligatoire.',
            'unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'unit_price.min' => 'Le prix unitaire ne peut pas être négatif.',
        ];
    }

    /* ============================================================
     |  MOUNT
     * ============================================================ */
    public function mount()
    {
        $this->date_commande = now()->format('Y-m-d');
        $this->reference = $this->generateReference();

        $this->fournisseurs = FournisseurModel::active()->get();
        $this->devises = DeviseModel::active()->get();
        $this->categories = Category::active()->get();

        $this->articles = ArticleModel::with('devise')->active()->get();

        // Get default devise and auto-select it
        $defaultDevise = DeviseModel::getDefaultDevise();
        if ($defaultDevise) {
            $this->devise_id = $defaultDevise->id;
            $this->taux_change = 1;
        }

        $this->syncAvailableArticles();
    }

    /* ============================================================
     |  ARTICLE SEARCH / SELECT
     * ============================================================ */
    public function updatedArticleSearch()
    {
        $this->filterArticles();
    }

    public function updatedArticleId($id)
    {
        $article = $this->articles->firstWhere('id', $id);
        $this->unit_price = $article?->prix_achat ?? null;
    }

    protected function syncAvailableArticles()
    {
        $usedIds = collect($this->lignes)->pluck('article_id');

        $this->availableArticles = $this->articles
            ->whereNotIn('id', $usedIds)
            ->values();

        $this->filterArticles();
    }

    protected function filterArticles()
    {
        if (strlen($this->articleSearch) < 2) {
            $this->filteredArticles = $this->availableArticles;
            return;
        }

        $search = strtolower($this->articleSearch);

        $this->filteredArticles = $this->availableArticles
            ->filter(
                fn($a) =>
                str_contains(strtolower($a->reference), $search) ||
                    str_contains(strtolower($a->designation), $search)
            )
            ->values();
    }

    /* ============================================================
     |  LIGNES
     * ============================================================ */
    public function addLigne()
    {
        $this->validate([
            'article_id' => 'required|exists:article_models,id',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
        ],  [
            'article_id.required' => 'Veuillez sélectionner un article.',
            'article_id.exists' => 'L\'article sélectionné est invalide.',
            'quantity.required' => 'La quantité est obligatoire.',
            'quantity.numeric' => 'La quantité doit être un nombre.',
            'quantity.min' => 'La quantité doit être au moins 1.',
            'unit_price.required' => 'Le prix unitaire est obligatoire.',
            'unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'unit_price.min' => 'Le prix unitaire ne peut pas être négatif.',
        ]);

        $article = $this->articles->firstWhere('id', $this->article_id);

        $this->lignes[] = [
            'article_id' => $article->id,
            'article_code' => $article->reference,
            'article_name' => $article->designation,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->quantity * $this->unit_price,
        ];

        $this->reset(['article_id', 'quantity', 'unit_price', 'articleSearch']);
        $this->syncAvailableArticles();
    }

    public function removeLigne($index)
    {
        unset($this->lignes[$index]);
        $this->lignes = array_values($this->lignes);
        $this->syncAvailableArticles();
    }

    public function updateLigneQuantity($index, $qty)
    {
        if (!isset($this->lignes[$index])) return;

        $this->lignes[$index]['quantity'] = max(1, $qty);
        $this->lignes[$index]['subtotal'] =
            $this->lignes[$index]['quantity'] * $this->lignes[$index]['unit_price'];
    }

    public function incrementQuantity($index)
    {
        if (!isset($this->lignes[$index])) return;

        $this->lignes[$index]['quantity']++;
        $this->lignes[$index]['subtotal'] =
            $this->lignes[$index]['quantity'] * $this->lignes[$index]['unit_price'];
    }

    public function decrementQuantity($index)
    {
        if (!isset($this->lignes[$index])) return;

        $this->lignes[$index]['quantity'] = max(1, $this->lignes[$index]['quantity'] - 1);
        $this->lignes[$index]['subtotal'] =
            $this->lignes[$index]['quantity'] * $this->lignes[$index]['unit_price'];
    }

    public function updateLignePrice($index, $price)
    {
        if (!isset($this->lignes[$index])) return;

        $this->lignes[$index]['unit_price'] = max(0, $price);
        $this->lignes[$index]['subtotal'] =
            $this->lignes[$index]['quantity'] * $price;
    }

    /* ============================================================
     |  TOTAL
     * ============================================================ */
    public function getTotalAmount()
    {
        $total = collect($this->lignes)->sum('subtotal');

        return $this->remise > 0
            ? $total * (1 - $this->remise / 100)
            : $total;
    }

    /* ============================================================
     |  RESET FORM METHOD
     * ============================================================ */
    public function resetForm()
    {
        $this->reset([
            'reference',
            'fournisseur_id',
            'devise_id',
            'taux_change',
            'remise',
            'date_commande',
            'status',
            'article_id',
            'quantity',
            'unit_price',
            'lignes',
            'articleSearch'
        ]);

        $this->reference = $this->generateReference();
        $this->date_commande = now()->format('Y-m-d');
        $this->taux_change = 1;
        $this->remise = 0;
        $this->status = 'EN_COURS';

        $this->syncAvailableArticles();

        $this->dispatch('success', message: 'Formulaire réinitialisé avec succès');
    }

    /* ============================================================
     |  SAVE COMMANDE
     * ============================================================ */
    public function save()
    {
        $this->validate();

        if (empty($this->lignes)) {
            $this->dispatch('error', message: 'Ajoutez au moins une ligne');
            return;
        }

        DB::beginTransaction();

        try {
            $commande = CommandeFournisseur::create([
                'reference' => $this->reference,
                'fournisseur_id' => $this->fournisseur_id,
                'devise_id' => $this->devise_id,
                'taux_change' => $this->taux_change,
                'remise' => $this->remise,
                'date_commande' => $this->date_commande,
                'status' => $this->status,
                'created_by' => Auth::id(),
            ]);

            foreach ($this->lignes as $ligne) {
                LigneCommandeFournisseur::create([
                    'commande_id' => $commande->id,
                    'article_id' => $ligne['article_id'],
                    'quantity' => $ligne['quantity'],
                    'unit_price' => $ligne['unit_price'],
                ]);
            }

            DB::commit();

            logActivity("Création commande fournisseur", [], $commande);

            return redirect()->route('stock.commandes');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    /* ============================================================
     |  QUICK ARTICLE CREATION
     * ============================================================ */
    public function createArticle()
    {
        $this->resetNewArticle();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetNewArticle();
    }

    public function resetNewArticle()
    {
        $this->newArticle = [
            'reference' => 'AR' . rand(100, 999),
            'category_id' => '',
            'designation' => '',
            'description' => '',
            'prix_achat' => '',
            'prix_vente' => '',
            'unite' => '',
            'status' => true,
        ];
    }

    public function storeArticle()
    {
        $this->validate($this->articleRules);

        $article = ArticleModel::create([
            ...$this->newArticle,
            'devise_id' => $this->devise_id,
            'created_by' => Auth::id(),
        ]);

        $this->articles->push($article);
        $this->syncAvailableArticles();

        $this->showModal = false;

        $this->dispatch('success', message: 'Article créé avec succès');
    }

    /* ============================================================
     |  UTILS
     * ============================================================ */
    protected function generateReference()
    {
        return 'CMD-' . now()->format('ymd') . '-' . rand(100, 999);
    }

    public function render()
    {
        view()->share('title', "Nouvelle Commande Fournisseur");
        view()->share('breadcrumb', "Créer Commande");

        return view('livewire.stock.create-commande', [
            'totalAmount' => $this->getTotalAmount(),
        ]);
    }
}
