<?php

namespace App\Livewire\Ventes;

use Livewire\Component;
use App\Models\Ventes\VenteModel;
use App\Models\Ventes\LigneVenteClient;
use App\Models\ClientModel;
use App\Models\Articles\ArticleModel;
use App\Models\DeviseModel;
use App\Models\Stock\LigneReceptionFournisseur;
use App\Models\Ventes\VentePaiementClient;
use App\Models\Warehouse\EtagereModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateVente extends Component
{
    public $reference;
    public $client_id;
    public $devise_id;
    public $date_facture;
    public $type_vente = 'DETAIL';
    public $remise = 0;

    public $venteId;
    public $showPaiementForm = false;

    // paiement fields
    public $paiement_date;
    public $paiement_montant = 0;
    public $mode_paiement;
    public $paiement_notes;

    public $clients = [];
    public $articles = [];
    public $devises = [];

    public $lignes = [];

    protected $rules = [
        'client_id' => 'required|exists:client_models,id',
        'devise_id' => 'required|exists:devise_models,id',
        'date_facture' => 'required|date',
        'remise' => 'numeric|min:0|max:100',
        'lignes.*.article_id' => 'required|exists:article_models,id',
        'lignes.*.etagere_id' => 'required|exists:etagere_models,id',
        'lignes.*.quantity' => 'required|numeric|min:1',
        'lignes.*.unit_price' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'client_id.required' => 'Le client est requis.',
        'devise_id.required' => 'La devise est requise.',
        'lignes.*.article_id.required' => 'L\'article est requis.',
        'lignes.*.etagere_id.required' => 'L\'étagère est requise.',
        'lignes.*.quantity.required' => 'La quantité est requise.',
        'lignes.*.quantity.min' => 'La quantité doit être au moins 1.',
        'lignes.*.unit_price.required' => 'Le prix unitaire est requis.',
        'lignes.*.unit_price.min' => 'Le prix unitaire doit être positif.',
    ];

    public function mount()
    {
        $this->clients  = ClientModel::active()->orderBy('name')->get();
        $this->articles = ArticleModel::active()->orderBy('designation')->get();
        $this->devises = DeviseModel::active()->orderBy('code')->get();

        $this->date_facture = now()->format('Y-m-d');
        $this->paiement_date = now()->format('Y-m-d');

        $this->reference = $this->generateReference();

        $this->addLine();
    }

    /* ===================== REFERENCE ===================== */

    private function generateReference()
    {
        $year = now()->format('y');
        $count = VenteModel::whereYear('created_at', $year)->count() + 1;
        $rand = rand(10, 99);

        return 'V' . '-' . $rand . '' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /* ===================== LINES ===================== */

    public function addLine()
    {
        $this->lignes[] = [
            'article_id'  => null,
            'etagere_id'  => null,
            'quantity'    => 0,
            'unit_price'  => 0,
            'available'   => 0,
        ];
    }

    public function removeLine($index)
    {
        unset($this->lignes[$index]);
        $this->lignes = array_values($this->lignes);
    }

    public function subTotal()
    {
        return collect($this->lignes)->sum(
            fn($l) => ((float) $l['quantity'] ?? 0) * ((float) $l['unit_price'] ?? 0)
        );
    }

    public function remiseAmount()
    {
        return $this->subTotal() * ((float) $this->remise / 100);
    }

    public function updatedRemise($value)
    {
        $this->remise = max(0, min(100, (float) $value));
    }

    public function totalAfterRemise()
    {
        return $this->subTotal() - $this->remiseAmount();
    }

    /* ===================== STOCK ===================== */

    public function updatedLignes($value, $key)
    {
        [$index, $field] = explode('.', $key);

        if (in_array($field, ['article_id', 'etagere_id'])) {
            $this->calculateAvailable($index);
        }

        // Auto-update unit price when article changes
        if ($field === 'article_id' && !empty($value)) {
            $article = ArticleModel::find($value);
            if ($article) {
                $this->lignes[$index]['unit_price'] = $article->prix_vente ?? 0;
            }
        }
    }

    private function calculateAvailable($index)
    {
        $line = $this->lignes[$index];

        if (!$line['article_id'] || !$line['etagere_id']) {
            $this->lignes[$index]['available'] = 0;
            return;
        }

        // Pass the current index to exclude it from form calculations
        $this->lignes[$index]['available'] = $this->availableQuantity(
            $line['article_id'],
            $line['etagere_id'],
            $index // Exclude current line from form calculations
        );
    }

    private function availableQuantity($articleId, $etagereId, $excludeIndex = null): int
    {
        $availableFromDB = $this->getDatabaseStock($articleId, $etagereId);

        // Subtract quantities currently entered in the form but not yet saved
        $reservedInForm = 0;
        foreach ($this->lignes as $index => $line) {
            // Skip if this is the line we're calculating for (optional)
            if ($excludeIndex !== null && $index === $excludeIndex) {
                continue;
            }

            if (
                $line['article_id'] == $articleId
                && $line['etagere_id'] == $etagereId
                && !empty($line['quantity'])
            ) {
                $reservedInForm += (int) $line['quantity'];
            }
        }

        return max(0, $availableFromDB - $reservedInForm);
    }

    public function getEtageresProperty()
    {
        return collect($this->lignes)->mapWithKeys(function ($ligne, $index) {
            if (empty($ligne['article_id'])) {
                return [$index => collect()];
            }

            $etageres = EtagereModel::with('magasin')
                ->whereHas('ligneReceptions', function ($query) use ($ligne) {
                    $query->where('article_id', $ligne['article_id']);
                })
                ->orWhereHas('ligneVentes', function ($query) use ($ligne) {
                    $query->where('article_id', $ligne['article_id']);
                })
                ->get()
                ->unique('id')
                ->map(function ($etagere) use ($ligne, $index) {
                    return [
                        'id' => $etagere->id,
                        'code' => $etagere->code_etagere,
                        'magasin' => $etagere->magasin?->nom,
                        'available' => $this->availableQuantity(
                            $ligne['article_id'],
                            $etagere->id,
                            $index // Exclude current line
                        ),
                    ];
                });

            return [$index => $etageres];
        });
    }

    /* ===================== SAVE ===================== */

    /**
     * Validate stock availability for all lines in the form
     */
    private function validateStockAvailability(): array
    {
        $errors = [];

        // Group quantities by article and shelf across ALL lines
        $requestedQuantities = [];
        foreach ($this->lignes as $line) {
            if (!empty($line['article_id']) && !empty($line['etagere_id'])) {
                $key = $line['article_id'] . '-' . $line['etagere_id'];
                $requestedQuantities[$key] = ($requestedQuantities[$key] ?? 0) + (int) $line['quantity'];
            }
        }

        // Validate stock for each unique article-shelf combination
        foreach ($requestedQuantities as $key => $totalRequested) {
            [$articleId, $etagereId] = explode('-', $key);

            // Get ACTUAL available stock from database (excluding current form)
            $availableFromDB = $this->getDatabaseStock($articleId, $etagereId);

            // Compare total requested against actual database stock
            if ($totalRequested > $availableFromDB) {
                $article = ArticleModel::find($articleId);
                $etagere = EtagereModel::find($etagereId);

                // Find which lines use this article-shelf combination
                foreach ($this->lignes as $index => $line) {
                    if ($line['article_id'] == $articleId && $line['etagere_id'] == $etagereId) {
                        $errors["lignes.$index.quantity"] =
                            "Stock insuffisant pour {$article->designation} sur {$etagere->code_etagere}. " .
                            "Total demandé: {$totalRequested}, Disponible: {$availableFromDB}";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get actual stock from database for an article-shelf combination
     */
    private function getDatabaseStock($articleId, $etagereId): int
    {
        $received = LigneReceptionFournisseur::where('article_id', $articleId)
            ->where('etagere_id', $etagereId)
            ->sum('quantity');

        $sold = LigneVenteClient::where('article_id', $articleId)
            ->where('etagere_id', $etagereId)
            ->whereHas('vente', fn($q) => $q->where('status', '!=', 'ANNULEE'))
            ->sum('quantity');

        return max(0, $received - $sold);
    }

    /**
     * Create sale lines in database
     */
    private function createSaleLines($venteId): void
    {
        foreach ($this->lignes as $line) {
            $etagere = EtagereModel::findOrFail($line['etagere_id']);

            LigneVenteClient::create([
                'vente_id'   => $venteId,
                'article_id' => $line['article_id'],
                'etagere_id' => $line['etagere_id'],
                'magasin_id' => $etagere->magasin_id,
                'quantity'   => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'total'      => $line['quantity'] * $line['unit_price'],
            ]);
        }
    }

    public function store()
    {
        $this->validate();

        // Validate stock availability
        $stockErrors = $this->validateStockAvailability();
        if (!empty($stockErrors)) {
            foreach ($stockErrors as $field => $message) {
                $this->addError($field, $message);
            }
            return;
        }

        DB::beginTransaction();

        try {
            // ✅ CREATE VENTE
            $vente = VenteModel::create([
                'reference'   => $this->reference,
                'client_id'   => $this->client_id,
                'devise_id'   => $this->devise_id,
                'date_facture' => $this->date_facture,
                'type_vente'  => $this->type_vente,
                'remise'      => $this->remise,
                'total'       => $this->totalAfterRemise(),
                'status'      => 'IMPAYEE',
                'created_by'  => Auth::id(),
            ]);

            // Create sale lines
            $this->createSaleLines($vente->id);

            DB::commit();

            $this->venteId = $vente->id;
            $this->showPaiementForm = true;

            // Reset payment amount when showing payment form
            $this->paiement_montant = $this->totalAfterRemise();
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création de la vente: ' . $e->getMessage());
        }
    }

    public function storePaiement()
    {
        $this->validate([
            'paiement_date' => 'required|date',
            'paiement_montant' => [
                'required',
                'numeric',
                'min:0',
                'max:' . $this->totalAfterRemise()
            ],
            'mode_paiement' => 'required|in:ESPECES,VIREMENT,MOBILE MONEY',
        ], [
            'paiement_montant.max' => 'Le montant payé ne peut pas dépasser le montant dû.',
            'paiement_montant.required' => 'Le montant payé est requis.',
            'mode_paiement.required' => 'Le mode de paiement est requis.',
        ]);

        $vente = VenteModel::with('paiements')->findOrFail($this->venteId);

        DB::beginTransaction();

        try {
            VentePaiementClient::create([
                'vente_id' => $vente->id,
                'date_paiement' => $this->paiement_date,
                'montant' => $this->paiement_montant,
                'mode_paiement' => $this->mode_paiement,
                'reference' => 'PAY-' . strtoupper(uniqid()),
                'notes' => $this->paiement_notes,
                'created_by' => Auth::id(),
            ]);

            // Calculate total paid including this payment
            $totalPaid = $vente->paiements()->sum('montant') + $this->paiement_montant;

            // Update vente status
            if (abs($totalPaid - $vente->total) < 0.01) { // Using float comparison with tolerance
                $vente->status = 'PAYEE';
            } elseif ($totalPaid > 0) {
                $vente->status = 'PARTIELLE';
            } else {
                $vente->status = 'IMPAYEE';
            }

            $vente->save();

            DB::commit();

            session()->flash('success', 'Paiement effectué avec succès');
            return redirect()->route('ventes.ventes');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    public function render()
    {
        view()->share('title', "Gestion des ventes");
        view()->share('breadcrumb', "Ajouter vente");

        return view('livewire.ventes.create-vente');
    }
}
