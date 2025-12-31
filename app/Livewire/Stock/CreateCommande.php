<?php

namespace App\Livewire\Stock;

use App\Models\Articles\ArticleModel;
use App\Models\DeviseModel;
use App\Models\FournisseurModel;
use App\Models\Stock\CommandeFournisseur;
use App\Models\Stock\LigneCommandeFournisseur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Validation\Rule;

class CreateCommande extends Component
{
    // Commande properties
    public $reference;
    public $fournisseur_id;
    public $devise_id;
    public $taux_change = 1;
    public $remise = 0;
    public $date_commande;
    public $status = 'EN_COURS';

    // Ligne commande properties
    public $article_id;
    public $quantity = '';
    public $unit_price = '';

    // Lists
    public $lignes = [];
    public $fournisseurs = [];
    public $devises = [];
    public $articles = [];
    public $availableArticles = []; // Articles non ajoutés

    // UI State
    public $showCommandeForm = true;
    public $commandeCreated = false;

    protected function rules()
    {
        return [
            'reference' => 'required|string|unique:commande_fournisseurs,reference',
            'fournisseur_id' => 'required|exists:fournisseur_models,id',
            'devise_id' => 'required|exists:devise_models,id',
            'taux_change' => 'nullable|numeric|min:0',
            'remise' => 'nullable|numeric|min:0|max:100',
            'date_commande' => 'required|date',
            'status' => 'required|in:EN_COURS,PARTIELLE,TERMINEE,ANNULEE',
        ];
    }

    protected $messages = [
        'reference.required' => 'La référence est obligatoire',
        'reference.unique' => 'Cette référence existe déjà',
        'fournisseur_id.required' => 'Le fournisseur est obligatoire',
        'devise_id.required' => 'La devise est obligatoire',
        'date_commande.required' => 'La date de commande est obligatoire',
    ];

    public function mount()
    {
        $this->loadData();
        $this->date_commande = now()->format('Y-m-d');
        $this->reference = $this->generateReference();
        $this->updateAvailableArticles();
    }

    public function loadData()
    {
        $this->fournisseurs = FournisseurModel::active()->get();
        $this->devises = DeviseModel::active()->get();
        $this->articles = ArticleModel::active()->get();
    }

    // New method to update available articles (those not already in lines)
    public function updateAvailableArticles()
    {
        $addedArticleIds = collect($this->lignes)->pluck('article_id')->toArray();

        $this->availableArticles = ArticleModel::active()
            ->whereNotIn('id', $addedArticleIds)
            ->get();
    }

    // When article is selected, auto-fill unit price
    public function updatedArticleId($value)
    {
        if ($value) {
            $article = ArticleModel::find($value);
            if ($article) {
                $this->unit_price = $article->prix_achat;
            }
        } else {
            $this->unit_price = '';
        }
    }

    public function generateReference()
    {
        return 'CMD-' . rand(1000, 9999);
    }

    public function addLigne()
    {
        $this->validate([
            'article_id' => 'required|exists:article_models,id',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
        ], [
            'article_id.required' => 'L\'article est obligatoire',
            'quantity.required' => 'La quantité est obligatoire',
            'quantity.min' => 'La quantité doit être au moins 1',
            'unit_price.required' => 'Le prix unitaire est obligatoire',
            'unit_price.min' => 'Le prix unitaire doit être positif',
        ]);

        $article = ArticleModel::find($this->article_id);

        // Vérifier si l'article existe déjà dans les lignes
        $existingIndex = collect($this->lignes)->search(function ($ligne) {
            return $ligne['article_id'] == $this->article_id;
        });

        if ($existingIndex !== false) {
            // Mettre à jour la quantité si l'article existe déjà
            $this->lignes[$existingIndex]['quantity'] += $this->quantity;
            $this->lignes[$existingIndex]['subtotal'] = $this->lignes[$existingIndex]['quantity'] * $this->lignes[$existingIndex]['unit_price'];

            $this->dispatch('success', message: 'Quantité mise à jour pour cet article');
        } else {
            // Ajouter une nouvelle ligne
            $this->lignes[] = [
                'article_id' => $this->article_id,
                'article_name' => $article->designation ?? 'N/A',
                'article_code' => $article->reference ?? 'N/A',
                'quantity' => $this->quantity,
                'unit_price' => $this->unit_price,
                'subtotal' => $this->quantity * $this->unit_price,
            ];

            $this->dispatch('success', message: 'Ligne ajoutée avec succès');

            // Update available articles after adding
            $this->updateAvailableArticles();
        }

        // Reset ligne form
        $this->reset(['article_id', 'quantity', 'unit_price']);
    }

    public function removeLigne($index)
    {
        if (isset($this->lignes[$index])) {
            unset($this->lignes[$index]);
            $this->lignes = array_values($this->lignes);

            // Update available articles after removal
            $this->updateAvailableArticles();

            $this->dispatch('success', message: 'Ligne supprimée');
        }
    }

    public function updateLigneQuantity($index, $quantity)
    {
        if (isset($this->lignes[$index]) && $quantity > 0) {
            $this->lignes[$index]['quantity'] = $quantity;
            $this->lignes[$index]['subtotal'] = $quantity * $this->lignes[$index]['unit_price'];
        }
    }

    public function updateLignePrice($index, $price)
    {
        if (isset($this->lignes[$index]) && $price >= 0) {
            $this->lignes[$index]['unit_price'] = $price;
            $this->lignes[$index]['subtotal'] = $this->lignes[$index]['quantity'] * $price;
        }
    }

    public function getTotalAmount()
    {
        $total = collect($this->lignes)->sum('subtotal');

        // Convertir la remise en nombre, défaut 0
        $remise = is_numeric($this->remise) ? floatval($this->remise) : 0;

        if ($remise > 0) {
            $total = $total - ($total * ($remise / 100));
        }

        return $total;
    }

    public function save()
    {
        // Valider la commande
        $this->validate();

        // Vérifier qu'il y a au moins une ligne
        if (empty($this->lignes)) {
            $this->dispatch('error', message: 'Veuillez ajouter au moins une ligne de commande');
            return;
        }

        DB::beginTransaction();

        try {
            // Créer la commande
            $commande = CommandeFournisseur::create([
                'reference' => $this->reference,
                'fournisseur_id' => $this->fournisseur_id,
                'devise_id' => $this->devise_id,
                'taux_change' => $this->taux_change,
                'remise' => $this->remise,
                'date_commande' => $this->date_commande,
                'status' => $this->status,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Créer les lignes
            foreach ($this->lignes as $ligne) {
                LigneCommandeFournisseur::create([
                    'commande_id' => $commande->id,
                    'article_id' => $ligne['article_id'],
                    'quantity' => $ligne['quantity'],
                    'unit_price' => $ligne['unit_price'],
                ]);
            }

            DB::commit();

            $this->dispatch('success', message: 'Commande créée avec succès');

            // Redirection
            return redirect()->route('stock.commandes');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

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
            'lignes'
        ]);

        $this->date_commande = now()->format('Y-m-d');
        $this->reference = $this->generateReference();
        $this->taux_change = 1;
        $this->remise = 0;
        $this->status = 'EN_COURS';
        $this->updateAvailableArticles();
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
