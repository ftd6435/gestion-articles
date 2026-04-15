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
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CreateVente extends Component
{
    public $reference;
    public $client_id;
    public $devise_id;

    public $currency = 'FG';

    public $date_facture;
    public $type_vente = 'DETAIL';
    public $remise = 0;

    public $venteId;
    public $showPaiementForm = false;

    // Searchable client input
    public $clientSearch = '';
    public $showClientDropdown = false;
    public $filteredClients = [];
    public $selectedClient = null;
    public $clientName = '';
    public $clientTelephone = '';

    // Searchable article input for each line
    public $articleSearches = [];
    public $showArticleDropdowns = [];
    public $filteredArticles = [];

    // Creating a new client
    public $showModal = false;

    public $clientId;
    public $name;
    public $type = 'DETAILLANT';
    public $telephone;
    public $email;
    public $adresse;
    public $status = true;

    // paiement fields
    public $paiement_date;
    public $paiement_montant = 0;
    public $mode_paiement;
    public $paiement_notes;

    public $clients;

    public $articles;

    public $devises;

    public $lignes = [];

    // Define a Line array structure (CHANGÉ: tableau au lieu d'objet)
    protected $lineStructure = [
        'article_id' => null,
        'etagere_id' => null,
        'quantity' => 0,
        'unit_price' => 0,
        'available' => 0,
        'article_designation' => '',
        'article_reference' => '',
    ];

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
        // Initialize as empty collections
        $this->clients = new Collection();
        $this->articles = new Collection();
        $this->devises = new Collection();

        // Load data
        $this->loadClients();
        $this->loadArticles();
        $this->loadDevises();

        $defaultClient = $this->clients->firstWhere('is_default', true);
        if ($defaultClient) {
            $this->selectClient($defaultClient->id);
        }

        $defaultDevise = DeviseModel::getDefaultDevise();
        if ($defaultDevise) {
            $this->devise_id = $defaultDevise->id;
            $this->currency = $defaultDevise->symbole ?? $defaultDevise->code;
        }

        $this->date_facture = now()->format('Y-m-d');
        $this->paiement_date = now()->format('Y-m-d');

        $this->reference = $this->generateReference();

        $this->addLine();
    }

    /**
     * Load clients from database
     */
    private function loadClients(): void
    {
        $this->clients = ClientModel::active()->orderBy('name')->get();
        $this->filteredClients = $this->clients->take(10)->toArray();
    }

    /**
     * Load articles from database
     */
    private function loadArticles(): void
    {
        $this->articles = ArticleModel::active()->orderBy('designation')->get();
        $this->filteredArticles = $this->articles->take(10)->toArray();
    }

    /**
     * Load devises from database
     */
    private function loadDevises(): void
    {
        $this->devises = DeviseModel::active()->orderBy('code')->get();
    }

    /* ===================== REFERENCE ===================== */

    private function generateReference(): string
    {
        $year = now()->format('y');
        $count = VenteModel::count() + 1;
        $rand = rand(10, 99);

        return 'V' . '-' . $rand . '' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /* ================ SEARCHABLE CLIENT SELECT ====================*/

    public function updatedClientSearch(): void
    {
        if (empty($this->clientSearch)) {
            $this->filteredClients = $this->clients->take(10)->toArray();
            $this->showClientDropdown = true;
            return;
        }

        $search = strtolower($this->clientSearch);
        $this->filteredClients = $this->clients
            ->filter(function ($client) use ($search) {
                return str_contains(strtolower($client->name), $search) ||
                    str_contains(strtolower($client->telephone), $search) ||
                    str_contains(strtolower($client->email), $search);
            })
            ->take(10)
            ->toArray();

        $this->showClientDropdown = true;
    }

    public function selectClient($clientId): void
    {
        $client = $this->clients->firstWhere('id', $clientId);
        if ($client) {
            $this->client_id = $client->id;
            $this->clientName = $client->name;
            $this->clientTelephone = $client->telephone;
            $this->clientSearch = $client->name . ' - ' . $client->telephone;
            $this->showClientDropdown = false;
        }
    }

    public function clearClient(): void
    {
        $this->client_id = null;
        $this->clientName = '';
        $this->clientTelephone = '';
        $this->clientSearch = '';
        $this->showClientDropdown = false;
    }

    public function closeClientDropdown(): void
    {
        // Delay closing to allow click on dropdown items
        $this->dispatch('dropdown-close-delay');
    }

    /* ================ SEARCHABLE ARTICLE SELECT ====================*/

    public function updatedArticleSearches($value, $key): void
    {
        $index = str_replace('articleSearches.', '', $key);

        if (empty($value)) {
            $this->filteredArticles[$index] = $this->articles->take(10)->toArray();
            $this->showArticleDropdowns[$index] = true;
            return;
        }

        $search = strtolower($value);
        $this->filteredArticles[$index] = $this->articles
            ->filter(function ($article) use ($search) {
                return str_contains(strtolower($article->designation), $search) ||
                    str_contains(strtolower($article->reference), $search) ||
                    str_contains(strtolower($article->code_barre), $search);
            })
            ->take(10)
            ->toArray();

        $this->showArticleDropdowns[$index] = true;
    }

    public function selectArticle($index, $articleId): void
    {
        $article = $this->articles->firstWhere('id', $articleId);
        if ($article) {
            $this->lignes[$index]['article_id'] = $article->id;
            $this->lignes[$index]['article_designation'] = $article->designation;
            $this->lignes[$index]['article_reference'] = $article->reference;
            $this->lignes[$index]['unit_price'] = $article->prix_vente ?? 0;

            // Auto-select the linked shelf with the highest available stock.
            $bestEtagereId = $this->getBestEtagereForArticle((int) $article->id, (int) $index);
            $this->lignes[$index]['etagere_id'] = $bestEtagereId ? (string) $bestEtagereId : null;

            $this->articleSearches[$index] = $article->designation . ' (' . $article->reference . ')';
            $this->showArticleDropdowns[$index] = false;

            // Recalculate available quantity
            $this->calculateAvailable($index);
        }
    }

    public function clearArticle($index): void
    {
        $this->lignes[$index]['article_id'] = null;
        $this->lignes[$index]['article_designation'] = '';
        $this->lignes[$index]['article_reference'] = '';
        $this->lignes[$index]['unit_price'] = 0;
        $this->lignes[$index]['available'] = 0;
        $this->lignes[$index]['etagere_id'] = null;
        $this->articleSearches[$index] = '';
        $this->showArticleDropdowns[$index] = false;
    }

    public function closeArticleDropdown($index): void
    {
        $this->dispatch('article-dropdown-close-delay', index: $index);
    }

    /* ================ ADD A NEW CLIENT ====================*/

    public function openClientModal(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('clients', 'create')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de créer des clients.');
            return;
        }

        $this->selectedClient = null;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetClientForm();
    }

    public function resetClientForm(): void
    {
        $this->reset(['name', 'telephone', 'type', 'email', 'adresse', 'clientId']);
        $this->status = true;
    }

    public function storeClient(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser?->canAccess('clients', 'create')) {
            $this->dispatch('error', message: 'Vous n\'avez pas la permission de créer des clients.');
            return;
        }

        $this->validate([
            'name' => 'required|string|min:3|max:100',
            'telephone' => [
                'required',
                'string',
                'min:6',
                'max:30',
                'regex:/^[0-9]+$/',
                Rule::unique('client_models', 'telephone')->ignore($this->clientId),
            ],
            'type' => ['required', Rule::in(['GROSSISTE', 'DETAILLANT', 'MIXTE'])],
            'email' => 'nullable|email|max:100',
            'adresse' => 'nullable|string|max:100',
            'status' => 'boolean',
        ]);

        try {
            $client = ClientModel::create([
                'name'       => $this->name,
                'telephone'  => $this->telephone,
                'type'       => $this->type,
                'email'      => $this->email,
                'adresse'    => $this->adresse,
                'status'     => $this->status,
                'created_by' => Auth::id(),
            ]);

            // Log activity with French description
            logActivity(
                'création rapide d\'un client',
                [
                    'client_id' => $client->id,
                    'nom' => $this->name,
                    'telephone' => $this->telephone,
                ],
                $client // Passer l'objet au lieu de la classe
            );

            // Reload clients and update filtered list
            $this->loadClients();

            // Auto-select the new client
            $this->selectClient($client->id);

            $this->closeModal();

            $this->dispatch(
                'success',
                message: "Le client \"{$this->name}\" a été ajouté avec succès."
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'error',
                message: 'Erreur lors de l\'ajout du client : ' . $e->getMessage()
            );
        }
    }

    /* ===================== LINES ===================== */

    public function addLine(): void
    {
        $index = count($this->lignes);
        $this->lignes[] = $this->lineStructure; // CHANGÉ: tableau directement
        $this->articleSearches[$index] = '';
        $this->showArticleDropdowns[$index] = false;
        $this->filteredArticles[$index] = $this->articles->take(10)->toArray();
    }

    public function removeLine(int $index): void
    {
        unset($this->lignes[$index]);
        unset($this->articleSearches[$index]);
        unset($this->showArticleDropdowns[$index]);
        unset($this->filteredArticles[$index]);

        $this->lignes = array_values($this->lignes);
        $this->articleSearches = array_values($this->articleSearches);
        $this->showArticleDropdowns = array_values($this->showArticleDropdowns);
        $this->filteredArticles = array_values($this->filteredArticles);
    }

    public function getSubTotal(): float
    {
        $total = 0;
        foreach ($this->lignes as $line) {
            $quantity = (float) ($line['quantity'] ?? 0);
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $total += $quantity * $unitPrice;
        }
        return $total;
    }

    public function getRemiseAmount(): float
    {
        return $this->getSubTotal() * ((float) $this->remise / 100);
    }

    public function updatedRemise($value): void
    {
        $this->remise = max(0, min(100, (float) $value));
    }

    public function updatedDeviseId($value)
    {
        $devise = DeviseModel::find($value);
        $this->currency = $devise->symbole ?? $devise->code;
    }

    public function getTotalAfterRemise(): float
    {
        return $this->getSubTotal() - $this->getRemiseAmount();
    }

    /* ===================== STOCK ===================== */

    public function updatedLignes($value, $key): void
    {
        [$index, $field] = explode('.', $key);

        if (in_array($field, ['article_id', 'etagere_id'])) {
            $this->calculateAvailable((int) $index);
        }

        // Auto-update unit price when article changes
        if ($field === 'article_id' && !empty($value)) {
            $article = $this->articles->firstWhere('id', $value);
            if ($article) {
                $this->lignes[$index]['unit_price'] = $article->prix_vente ?? 0;
            }
        }

        if ($field === 'quantity' && isset($this->lignes[$index]['quantity'])) {
            $this->lignes[$index]['quantity'] = (float) $this->lignes[$index]['quantity'];
        }

        if ($field === 'unit_price' && isset($this->lignes[$index]['unit_price'])) {
            $this->lignes[$index]['unit_price'] = (float) $this->lignes[$index]['unit_price'];
        }
    }

    private function calculateAvailable(int $index): void
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

    private function availableQuantity($articleId, $etagereId, ?int $excludeIndex = null): int
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

    public function getEtageresProperty(): array
    {
        $etageresByLine = [];

        foreach ($this->lignes as $index => $ligne) {
            if (empty($ligne['article_id'])) {
                $etageresByLine[$index] = collect();
                continue;
            }

            // Corrigez la requête pour utiliser AND au lieu de OR
            $etageres = EtagereModel::with('magasin')
                ->whereHas('ligneReceptions', function ($query) use ($ligne) {
                    $query->where('article_id', $ligne['article_id']);
                })
                ->get()
                ->filter(function ($etagere) use ($ligne) {
                    // Vérifiez qu'il y a du stock réel pour cet article
                    $stock = $this->getDatabaseStock($ligne['article_id'], $etagere->id);
                    return $stock > 0;
                })
                ->map(function ($etagere) use ($ligne, $index) {
                    return (object) [
                        'id' => $etagere->id,
                        'code' => $etagere->code_etagere,
                        'magasin' => $etagere->magasin?->nom,
                        'available' => $this->availableQuantity(
                            $ligne['article_id'],
                            $etagere->id,
                            $index
                        ),
                    ];
                })
                ->sortByDesc('available')
                ->values();

            $etageresByLine[$index] = $etageres;
        }

        return $etageresByLine;
    }

    private function getBestEtagereForArticle(int $articleId, int $lineIndex): ?int
    {
        $bestEtagereId = null;
        $bestAvailable = 0;

        $etageres = EtagereModel::query()
            ->whereHas('ligneReceptions', function ($query) use ($articleId) {
                $query->where('article_id', $articleId);
            })
            ->orderBy('code_etagere')
            ->get(['id']);

        foreach ($etageres as $etagere) {
            $available = $this->availableQuantity($articleId, $etagere->id, $lineIndex);

            if ($available > $bestAvailable) {
                $bestAvailable = $available;
                $bestEtagereId = (int) $etagere->id;
            }
        }

        return $bestEtagereId;
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
                $article = $this->articles->firstWhere('id', $articleId);
                $etagere = EtagereModel::find($etagereId);

                // Find which lines use this article-shelf combination
                foreach ($this->lignes as $index => $line) {
                    if ($line['article_id'] == $articleId && $line['etagere_id'] == $etagereId) {
                        $errors["lignes.$index.quantity"] =
                            "Stock insuffisant pour {$article?->designation} sur {$etagere?->code_etagere}. " .
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
            // Utilisez find() au lieu de findOrFail() et vérifiez si l'étagère existe
            $etagere = EtagereModel::find($line['etagere_id']);

            if (!$etagere) {
                throw new \Exception("L'étagère avec l'ID {$line['etagere_id']} n'existe pas.");
            }

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
        // Convertir pour le log
        $lignesArray = [];
        foreach ($this->lignes as $index => $line) {
            $lignesArray[$index] = [
                'article_id' => $line['article_id'] ?? null,
                'etagere_id' => $line['etagere_id'] ?? null,
                'quantity' => $line['quantity'] ?? null,
                'unit_price' => $line['unit_price'] ?? null,
                'article_designation' => $line['article_designation'] ?? '',
            ];
        }

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
                'total'       => $this->getTotalAfterRemise(),
                'status'      => 'IMPAYEE',
                'created_by'  => Auth::id(),
            ]);

            // Log vente creation with French description - CORRIGÉ: passer l'objet
            logActivity(
                'Création nouvelle vente',
                [
                    'vente_id' => $vente->id,
                    'reference' => $this->reference,
                    'client_id' => $this->client_id,
                    'montant_total' => $this->getTotalAfterRemise(),
                    'devise_id' => $this->devise_id,
                ],
                $vente // Passer l'objet au lieu de la classe
            );

            // Create sale lines
            $this->createSaleLines($vente->id);

            DB::commit();

            $this->venteId = $vente->id;
            $this->showPaiementForm = true;

            // Reset payment amount when showing payment form
            $this->paiement_montant = $this->getTotalAfterRemise();
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch(
                'error',
                message: 'Erreur lors de la création de la vente: ' . $e->getMessage()
            );
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
                'max:' . $this->getTotalAfterRemise()
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
            $paiement = VentePaiementClient::create([
                'vente_id' => $vente->id,
                'date_paiement' => $this->paiement_date,
                'montant' => $this->paiement_montant,
                'mode_paiement' => $this->mode_paiement,
                'reference' => 'PAY-' . rand(1000, 9999),
                'notes' => $this->paiement_notes,
                'created_by' => Auth::id(),
            ]);

            // Log payment creation with French description - CORRIGÉ: passer l'objet
            logActivity(
                'Paiement client enregistré',
                [
                    'paiement_id' => $paiement->id,
                    'reference' => $paiement->reference,
                    'vente_id' => $vente->id,
                    'vente_ref' => $vente->reference,
                    'montant' => $this->paiement_montant,
                    'mode_paiement' => $this->mode_paiement,
                ],
                $paiement
            );

            $vente->refresh();
            $totalPaid = (float) $vente->paiements()->sum('montant');
            $totalDue = (float) $vente->totalAfterRemise();

            $tolerance = 0.01;
            $oldStatus = $vente->status;

            if (abs($totalDue - $totalPaid) <= $tolerance) {
                $vente->status = 'PAYEE';
            } elseif ($totalPaid > 0) {
                $vente->status = 'PARTIELLE';
            } else {
                $vente->status = 'IMPAYEE';
            }

            // Log status change if it changed - CORRIGÉ: passer l'objet
            if ($oldStatus !== $vente->status) {
                logActivity(
                    'Statut vente mis à jour',
                    [
                        'vente_id' => $vente->id,
                        'reference' => $vente->reference,
                        'ancien_statut' => $oldStatus,
                        'nouveau_statut' => $vente->status,
                        'montant_paye' => $totalPaid,
                    ],
                    $vente // Passer l'objet au lieu de la classe
                );
            }

            $vente->save();

            DB::commit();

            $this->dispatch(
                'success',
                message: 'Paiement effectué avec succès'
            );

            return redirect()->route('ventes.ventes');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch(
                'error',
                message: 'Erreur lors du paiement: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        view()->share('title', "Gestion des ventes");
        view()->share('breadcrumb', "Ajouter vente");

        return view('livewire.ventes.create-vente');
    }
}
