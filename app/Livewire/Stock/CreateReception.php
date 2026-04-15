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
        $this->magasins = MagasinModel::active()
            ->with('etageres')
            ->orderByDesc('is_default')
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

    private function loadArticlesForSelectedCommande()
    {
        if (!$this->selectedCommande) {
            $this->articles = [];
            return;
        }

        // Charger uniquement les articles de la commande sélectionnée
        $this->articles = ArticleModel::whereHas('ligneCommandes', function ($query) {
            $query->where('commande_id', $this->commande_id);
        })
            ->orderBy('designation')
            ->get();
    }

    /** ================== REACTIVE ================== */
    public function updatedCommandeId()
    {
        $this->selectedCommande = CommandeFournisseur::with([
            'ligneCommandes.article',
            'receptions.ligneReceptions'
        ])->find($this->commande_id);

        // Charger uniquement les articles de cette commande
        $this->loadArticlesForSelectedCommande();

        $this->lines = [];
        $this->reset(['article_id', 'magasin_id', 'etagere_id', 'quantity', 'date_expiration']);

        // Log de sélection de commande
        if ($this->selectedCommande) {
            logActivity(
                'SELECT_COMMANDE_RECEPTION',
                [
                    'commande_id' => $this->commande_id,
                    'commande_ref' => $this->selectedCommande->reference,
                    'fournisseur' => $this->selectedCommande->fournisseur->name ?? 'N/A',
                    'status' => $this->selectedCommande->status,
                ],
                $this->selectedCommande
            );
        }
    }

    public function updatedMagasinId()
    {
        if (!$this->magasin_id) {
            $this->etageres = [];
            $this->etagere_id = null;
            return;
        }

        $this->etageres = EtagereModel::active()
            ->where('magasin_id', $this->magasin_id)
            ->orderByDesc('is_default')
            ->orderBy('code_etagere')
            ->get();

        $etagereId = EtagereModel::active()
            ->where('magasin_id', $this->magasin_id)
            ->orderByDesc('is_default')
            ->orderBy('code_etagere')
            ->value('id');
        $this->etagere_id = $etagereId ? (string) $etagereId : null;
    }

    public function updatedArticleId(): void
    {
        if (!$this->article_id) {
            return;
        }

        $defaultMagasinId = MagasinModel::active()
            ->orderByDesc('is_default')
            ->orderBy('nom')
            ->value('id');

        if (!$defaultMagasinId) {
            return;
        }

        $this->magasin_id = (string) $defaultMagasinId;
        $this->updatedMagasinId();
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
        if (!$this->selectedCommande) {
            $this->dispatch('error', message: 'Veuillez sélectionner une commande avant d\'ajouter des articles.');
            return;
        }

        if ($this->magasin_id && !$this->etagere_id) {
            $etagereId = EtagereModel::active()
                ->where('magasin_id', $this->magasin_id)
                ->orderByDesc('is_default')
                ->orderBy('code_etagere')
                ->value('id');
            $this->etagere_id = $etagereId ? (string) $etagereId : null;
        }

        $this->validate([
            'article_id' => 'required',
            'magasin_id' => 'required',
            'etagere_id' => 'required',
            'quantity' => 'required|numeric|min:1',
        ], [
            'article_id.required' => 'La sélection d\'un article est obligatoire.',
            'magasin_id.required' => 'La sélection d\'un magasin est obligatoire.',
            'etagere_id.required' => 'La sélection d\'une étagère est obligatoire.',
            'quantity.required' => 'La quantité est obligatoire.',
            'quantity.numeric' => 'La quantité doit être un nombre.',
            'quantity.min' => 'La quantité doit être au moins de 1.',
        ], [
            'article_id' => 'Article',
            'magasin_id' => 'Magasin',
            'etagere_id' => 'Étagère',
            'quantity' => 'Quantité',
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

        // Log d'ajout d'une ligne
        logActivity(
            "Ajout d'une ligne",
            [
                'article_id' => $article->id,
                'article_name' => $article->designation,
                'magasin' => $magasin->nom,
                'etagere' => $etagere->code_etagere,
                'quantity' => $this->quantity,
                'commande_id' => $this->commande_id,
                'remaining_qty' => $remaining - $this->quantity,
                'lines_count' => count($this->lines),
            ]
        );

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
        if (!isset($this->lines[$index])) {
            return;
        }

        $removedLine = $this->lines[$index];

        // Log de suppression d'une ligne
        logActivity(
            "Suppression d'une ligne",
            [
                'article_id' => $removedLine['article_id'],
                'article_name' => $removedLine['article_name'],
                'quantity' => $removedLine['quantity'],
                'commande_id' => $this->commande_id,
                'lines_count_before' => count($this->lines),
            ]
        );

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

        // Validation supplémentaire
        $this->validate([
            'commande_id' => 'required|exists:commande_fournisseurs,id',
        ], [
            'commande_id.required' => 'La commande est obligatoire.',
            'commande_id.exists' => 'La commande sélectionnée n\'existe pas.',
        ]);

        // Calcul du total des quantités
        $totalQuantity = array_sum(array_column($this->lines, 'quantity'));

        // Création de la réception
        $reception = ReceptionFournisseur::create([
            'reference' => 'REC-' . rand(1000, 9999),
            'commande_id' => $this->commande_id,
            'date_reception' => $this->date_reception,
            'created_by' => Auth::id(),
        ]);

        // Création des lignes de réception
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

        $newStatus = $completed ? 'TERMINEE' : 'PARTIELLE';
        $this->selectedCommande->update([
            'status' => $newStatus
        ]);

        // Log de création de réception
        logActivity(
            'Création de la réception',
            [
                'reception_id' => $reception->id,
                'reception_ref' => $reception->reference,
                'commande_id' => $this->commande_id,
                'commande_ref' => $this->selectedCommande->reference,
                'total_lines' => count($this->lines),
                'total_quantity' => $totalQuantity,
                'new_commande_status' => $newStatus,
                'articles' => array_map(function ($line) {
                    return [
                        'id' => $line['article_id'],
                        'name' => $line['article_name'],
                        'quantity' => $line['quantity'],
                        'magasin' => $line['magasin_name'],
                        'etagere' => $line['etagere_name'],
                    ];
                }, $this->lines),
            ],
            $reception
        );

        session()->flash('success', 'Réception enregistrée avec succès.');

        return redirect()->route('stock.approvisions');
    }

    public function render()
    {
        view()->share('title', "Gestion des réception des commandes");
        view()->share('breadcrumb', "Réception Commande");

        return view('livewire.stock.create-reception');
    }
}
