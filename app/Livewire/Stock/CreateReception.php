<?php

namespace App\Livewire\Stock;

use App\Models\Articles\ArticleModel;
use App\Models\Stock\CommandeFournisseur;
use App\Models\Stock\ReceptionFournisseur;
use App\Models\Stock\LigneReceptionFournisseur;
use App\Models\Warehouse\MagasinModel;
use App\Models\Warehouse\EtagereModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateReception extends Component
{
    /** ================== DATA ================== */
    public $commandes = [];
    public $articles = [];
    public $magasins = [];
    public $etageres = [];

    /** ================== RECEPTION ================== */
    public $commande_id;
    public $selectedCommande;
    public $date_reception;

    /** ================== LIGNE ================== */
    public $article_id;
    public $magasin_id;
    public $etagere_id;
    public $quantity;
    public $date_expiration;

    public $lines = [];

    /** ================== MOUNT ================== */
    public function mount()
    {
        $this->loadCommandes();
        $this->loadArticles();

        $this->magasins = MagasinModel::active()
            ->with('etageres')
            ->orderBy('nom')
            ->get();

        $this->date_reception = now()->format('Y-m-d');
    }

    /** ================== LOADERS ================== */
    private function loadCommandes()
    {
        $this->commandes = CommandeFournisseur::with('fournisseur')
            ->whereIn('status', ['EN_COURS', 'PARTIELLE'])
            ->orderByDesc('date_commande')
            ->get();
    }

    private function loadArticles()
    {
        $this->articles = ArticleModel::orderBy('designation')->get();
    }

    /** ================== REACTIVE ================== */
    public function updatedCommandeId()
    {
        $this->selectedCommande = CommandeFournisseur::with([
            'ligneCommandes.article',
            'receptions.ligneReceptions'
        ])->find($this->commande_id);

        $this->lines = [];
    }

    public function updatedMagasinId()
    {
        $this->etagere_id = null;

        $this->etageres = EtagereModel::active()
            ->where('magasin_id', $this->magasin_id)
            ->orderBy('code_etagere')
            ->get();
    }

    private function pendingQtyInCurrentReception($articleId): int
    {
        return collect($this->lines)
            ->where('article_id', $articleId)
            ->sum('quantity');
    }


    /** ================== BUSINESS ================== */
    private function alreadyReceivedQty($articleId)
    {
        return LigneReceptionFournisseur::whereHas('reception', function ($q) {
            $q->where('commande_id', $this->commande_id);
        })->where('article_id', $articleId)->sum('quantity');
    }

    public function addLine()
    {
        $this->validate([
            'article_id' => 'required',
            'magasin_id' => 'required',
            'etagere_id' => 'required',
            'quantity' => 'required|numeric|min:1',
        ]);

        $commandeLine = $this->selectedCommande
            ->ligneCommandes
            ->firstWhere('article_id', $this->article_id);

        if (!$commandeLine) {
            $this->addError('article_id', 'Article non présent dans la commande.');
            return;
        }

        $alreadyReceived = $this->alreadyReceivedQty($this->article_id);
        $pendingInForm   = $this->pendingQtyInCurrentReception($this->article_id);

        $remaining = $commandeLine->quantity - $alreadyReceived - $pendingInForm;


        if ($this->quantity > $remaining) {
            $this->addError(
                'quantity',
                "Quantité restante autorisée : {$remaining}"
            );
            return;
        }

        $article = $commandeLine->article;
        $magasin = MagasinModel::find($this->magasin_id);
        $etagere = EtagereModel::find($this->etagere_id);

        $this->lines[] = [
            'article_id' => $article->id,
            'article_name' => $article->designation,
            'magasin_id' => $magasin->id,
            'magasin_name' => $magasin->nom,
            'etagere_id' => $etagere->id,
            'etagere_name' => $etagere->code_etagere,
            'quantity' => $this->quantity,
            'date_expiration' => $this->date_expiration,
        ];

        $this->reset([
            'article_id',
            'magasin_id',
            'etagere_id',
            'quantity',
            'date_expiration',
            'etageres'
        ]);
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    /** ================== STORE ================== */
    public function store()
    {
        if (empty($this->lines)) {
            $this->addError('lines', 'Ajoutez au moins une ligne.');
            return;
        }

        $reception = ReceptionFournisseur::create([
            'commande_id' => $this->commande_id,
            'date_reception' => $this->date_reception,
            'created_by' => Auth::id(),
        ]);

        foreach ($this->lines as $line) {
            LigneReceptionFournisseur::create([
                'reception_id' => $reception->id,
                'article_id' => $line['article_id'],
                'magasin_id' => $line['magasin_id'],
                'etagere_id' => $line['etagere_id'],
                'quantity' => $line['quantity'],
                'date_expiration' => $line['date_expiration'],
            ]);
        }

        // 🔄 UPDATE COMMANDE STATUS
        $completed = true;

        foreach ($this->selectedCommande->ligneCommandes as $cmdLine) {
            $received = $this->alreadyReceivedQty($cmdLine->article_id);
            if ($received < $cmdLine->quantity) {
                $completed = false;
                break;
            }
        }

        $this->selectedCommande->update([
            'status' => $completed ? 'TERMINEE' : 'PARTIELLE'
        ]);

        session()->flash('success', 'Réception enregistrée avec succès.');

        return redirect()->route('stock.approvisions');
    }

    public function render()
    {
        return view('livewire.stock.create-reception');
    }
}
