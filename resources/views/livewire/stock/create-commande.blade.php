<div>
    <!-- Page Header - Improved mobile responsiveness -->
    <div class="mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div class="order-2 order-sm-1">
                <h1 class="h4 fw-bold mb-1">Nouvelle Commande Fournisseur</h1>
                <p class="text-muted small mb-0">
                    Créez une commande et ajoutez les lignes
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap order-1 order-sm-2">
                <button wire:click="createArticle" class="btn btn-success btn-sm btn-md">
                    <i class="fa fa-plus me-1"></i> <span class="d-none d-sm-inline">Nouvel</span> Article
                </button>
                <a href="{{ route('stock.commandes') }}" class="btn btn-outline-secondary btn-sm btn-md">
                    <i class="fas fa-arrow-left me-1 d-none d-sm-inline"></i>
                    <span class="d-sm-none">←</span>
                    <span class="d-none d-sm-inline">Retour</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile First Approach - Stack on small screens -->
    <div class="row g-4">
        <!-- Left Side: Commande Fournisseur Form -->
        <div class="col-12 col-lg-4 order-2 order-lg-1">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Informations Commande
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save" class="needs-validation" novalidate>
                        <!-- Référence -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                Référence <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input
                                    type="text"
                                    wire:model="reference"
                                    class="form-control form-control-sm @error('reference') is-invalid @enderror"
                                    readonly
                                    aria-label="Référence de commande">
                            </div>
                            @error('reference')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fournisseur -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                Fournisseur <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                <select
                                    wire:model="fournisseur_id"
                                    class="form-select form-select-sm @error('fournisseur_id') is-invalid @enderror"
                                    aria-label="Sélectionner un fournisseur">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($fournisseurs as $fournisseur)
                                        <option value="{{ $fournisseur->id }}">
                                            {{ Str::limit($fournisseur->name, 20) }} - {{ $fournisseur->telephone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('fournisseur_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Devise -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                Devise <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                <select
                                    wire:model.live="devise_id"
                                    class="form-select form-select-sm @error('devise_id') is-invalid @enderror"
                                    aria-label="Sélectionner une devise">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($devises as $devise)
                                        <option value="{{ $devise->id }}">
                                            {{ $devise->code }} - {{ $devise->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('devise_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Taux de change & Remise -->
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted mb-1">Taux de change</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        wire:model="taux_change"
                                        class="form-control form-control-sm"
                                        aria-label="Taux de change">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted mb-1">Remise (%)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        wire:model.live="remise"
                                        class="form-control form-control-sm"
                                        min="0"
                                        max="100"
                                        aria-label="Remise en pourcentage">
                                </div>
                            </div>
                        </div>

                        <!-- Date & Status -->
                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted mb-1">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input
                                        type="date"
                                        wire:model="date_commande"
                                        class="form-control form-control-sm @error('date_commande') is-invalid @enderror"
                                        aria-label="Date de commande">
                                </div>
                                @error('date_commande')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted mb-1">Status</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                    <select wire:model="status" class="form-select form-select-sm" aria-label="Statut de la commande">
                                        <option value="EN_COURS">En cours</option>
                                        <option value="ANNULEE">Annulée</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount Display -->
                        <div class="alert alert-info mt-4 py-2 px-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold small d-block">Total Commande:</span>
                                <small class="text-muted">Devise: {{ $devises->where('id', $devise_id)->first()->code ?? '---' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fs-5 fw-bold">{{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons - Improved for mobile -->
                        <div class="d-grid d-sm-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                            <button type="button" wire:click="resetForm" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo me-2"></i> Réinitialiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Ligne Commande Form & List -->
        <div class="col-12 col-lg-8 order-1 order-lg-2">
            <!-- Add Ligne Form - Compact on mobile -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        <span class="d-none d-sm-inline">Ajouter une</span> Ligne
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <!-- Article Search -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                Article <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input
                                    type="text"
                                    wire:model.live.debounce.200ms="articleSearch"
                                    class="form-control form-control-sm"
                                    placeholder="Rechercher un article..."
                                    aria-label="Rechercher un article">
                            </div>

                            <div class="mt-2">
                                <select
                                    wire:model.live="article_id"
                                    size="4"
                                    class="form-select form-select-sm @error('article_id') is-invalid @enderror"
                                    style="max-height: 150px; overflow-y: auto;"
                                    aria-label="Liste des articles disponibles">
                                    @if($articleSearch && $filteredArticles->isEmpty())
                                        <option value="" disabled class="text-muted">
                                            Aucun article trouvé pour "{{ $articleSearch }}"
                                        </option>
                                    @elseif($availableArticles->isEmpty())
                                        <option value="" disabled class="text-muted">
                                            Tous les articles ont été ajoutés
                                        </option>
                                    @else
                                        <option value="">-- Sélectionner un article --</option>
                                        @foreach($filteredArticles as $article)
                                            <option value="{{ $article->id }}" class="py-2">
                                                <div class="d-flex justify-content-between">
                                                    <span>{{ Str::limit($article->reference, 15) }}</span>
                                                    <span class="text-muted ms-2">{{ Str::limit($article->designation, 20) }}</span>
                                                </div>
                                                @if($article->prix_achat)
                                                    <small class="text-success">
                                                        {{ number_format($article->prix_achat, 2) }}
                                                        {{ $article->devise->symbole ?? '€' }}
                                                    </small>
                                                @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('article_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $filteredArticles->count() }} article(s) disponible(s)
                                </small>
                            </div>
                        </div>

                        <!-- Quantity & Price -->
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                Quantité <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                                <input
                                    type="number"
                                    wire:model="quantity"
                                    class="form-control form-control-sm @error('quantity') is-invalid @enderror"
                                    min="1"
                                    step="1"
                                    aria-label="Quantité">
                            </div>
                            @error('quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit Price -->
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold small text-muted mb-1">
                                Prix U. <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    {{ $devises->where('id', $devise_id)->first()->symbole ?? '€' }}
                                </span>
                                <input
                                    type="number"
                                    wire:model="unit_price"
                                    step="0.01"
                                    class="form-control form-control-sm @error('unit_price') is-invalid @enderror"
                                    placeholder="0.00"
                                    aria-label="Prix unitaire">
                            </div>
                            @error('unit_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Add Button -->
                        <div class="col-12 mt-2">
                            <button
                                type="button"
                                wire:click="addLigne"
                                class="btn btn-success btn-sm w-100"
                                :disabled="!article_id || !quantity || !unit_price">
                                <i class="fas fa-plus me-2"></i> Ajouter la ligne
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List of Lines -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Lignes de Commande
                    </h5>
                    <span class="badge bg-primary">{{ count($lignes) }} ligne(s)</span>
                </div>

                <div class="card-body p-0">
                    @if(empty($lignes))
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-1">Aucune ligne ajoutée</p>
                            <p class="small text-muted">Commencez par ajouter des articles ci-dessus</p>
                        </div>
                    @else
                        <!-- Desktop View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Article</th>
                                        <th width="15%" class="text-center">Qté</th>
                                        <th width="20%" class="text-end">Prix U.</th>
                                        <th width="20%" class="text-end">Sous-total</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lignes as $index => $ligne)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $ligne['article_code'] }}</div>
                                                <small class="text-muted">{{ Str::limit($ligne['article_name'], 30) }}</small>
                                            </td>
                                            <td class="text-center">
                                               <div class="d-flex align-items-center">
                                                    <button
                                                        class="btn btn-outline-secondary btn-sm px-2 py-1"
                                                        type="button"
                                                        wire:click="decrementQuantity({{ $index }})">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input
                                                        type="number"
                                                        wire:change="updateLigneQuantity({{ $index }}, $event.target.value)"
                                                        wire:blur="updateLigneQuantity({{ $index }}, $event.target.value)"
                                                        value="{{ $ligne['quantity'] }}"
                                                        class="form-control form-control-sm text-center mx-2"
                                                        min="1"
                                                        style="min-width: 80px;">
                                                    <button
                                                        class="btn btn-outline-secondary btn-sm px-2 py-1"
                                                        type="button"
                                                        wire:click="incrementQuantity({{ $index }})">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="input-group input-group-sm" style="width: 120px; margin-left: auto;">
                                                    <input
                                                        type="number"
                                                        wire:change="updateLignePrice({{ $index }}, $event.target.value)"
                                                        value="{{ $ligne['unit_price'] }}"
                                                        class="form-control form-control-sm text-end"
                                                        min="0"
                                                        step="0.01">
                                                    <span class="input-group-text bg-transparent border-0 p-0 px-1">
                                                        {{ $devises->where('id', $devise_id)->first()->symbole ?? '€' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ number_format($ligne['subtotal'], 2) }}
                                                <small class="text-muted d-block">
                                                    {{ $devises->where('id', $devise_id)->first()->code ?? '' }}
                                                </small>
                                            </td>
                                            <td>
                                                <button
                                                    wire:click="removeLigne({{ $index }})"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer"
                                                    aria-label="Supprimer la ligne">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Sous-total:</td>
                                        <td colspan="2" class="text-end fw-bold">
                                            {{ number_format(collect($lignes)->sum('subtotal'), 2) }}
                                            <small class="text-muted d-block">
                                                {{ $devises->where('id', $devise_id)->first()->code ?? '' }}
                                            </small>
                                        </td>
                                    </tr>
                                    @if($remise > 0)
                                        <tr>
                                            <td colspan="3" class="text-end">Remise ({{ $remise }}%):</td>
                                            <td colspan="2" class="text-end text-danger">
                                                - {{ number_format(collect($lignes)->sum('subtotal') * ($remise / 100), 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr class="table-primary">
                                        <td colspan="3" class="text-end fw-bold">Total Final:</td>
                                        <td colspan="2" class="text-end fw-bold fs-5">
                                            {{ number_format($totalAmount, 2) }}
                                            <small class="d-block">
                                                {{ $devises->where('id', $devise_id)->first()->code ?? '' }}
                                            </small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Mobile View - Improved -->
                        <div class="d-md-none">
                            @foreach($lignes as $index => $ligne)
                                <div class="border-bottom p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $ligne['article_code'] }}</div>
                                            <small class="text-muted">{{ Str::limit($ligne['article_name'], 25) }}</small>
                                        </div>
                                        <button
                                            wire:click="removeLigne({{ $index }})"
                                            class="btn btn-sm btn-outline-danger"
                                            aria-label="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="small text-muted mb-1 d-block">Quantité</label>
                                            <div class="input-group input-group-sm">
                                                <button
                                                    class="btn btn-outline-secondary btn-sm px-2"
                                                    type="button"
                                                    wire:click="decrementQuantity({{ $index }})">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input
                                                    type="number"
                                                    wire:change="updateLigneQuantity({{ $index }}, $event.target.value)"
                                                    value="{{ $ligne['quantity'] }}"
                                                    class="form-control form-control-sm text-center"
                                                    min="1"
                                                    style="max-width: 60px;">
                                                <button
                                                    class="btn btn-outline-secondary btn-sm px-2"
                                                    type="button"
                                                    wire:click="incrementQuantity({{ $index }})">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="small text-muted mb-1 d-block">Prix unitaire</label>
                                            <div class="input-group input-group-sm">
                                                <input
                                                    type="number"
                                                    wire:change="updateLignePrice({{ $index }}, $event.target.value)"
                                                    value="{{ $ligne['unit_price'] }}"
                                                    class="form-control form-control-sm"
                                                    min="0"
                                                    step="0.01">
                                                <span class="input-group-text">
                                                    {{ $devises->where('id', $devise_id)->first()->symbole ?? '€' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <div class="d-flex justify-content-between">
                                            <span class="small text-muted">Sous-total:</span>
                                            <span class="fw-bold">
                                                {{ number_format($ligne['subtotal'], 2) }}
                                                <small class="text-muted">
                                                    {{ $devises->where('id', $devise_id)->first()->code ?? '' }}
                                                </small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Mobile Summary -->
                            <div class="p-3 bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Sous-total:</span>
                                    <span class="fw-bold">
                                        {{ number_format(collect($lignes)->sum('subtotal'), 2) }}
                                        <small class="text-muted d-block">
                                            {{ $devises->where('id', $devise_id)->first()->code ?? '' }}
                                        </small>
                                    </span>
                                </div>
                                @if($remise > 0)
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>Remise ({{ $remise }}%):</span>
                                        <span>
                                            - {{ number_format(collect($lignes)->sum('subtotal') * ($remise / 100), 2) }}
                                        </span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between fw-bold pt-2 border-top">
                                    <span class="fs-6">Total Final:</span>
                                    <span class="text-success fs-5">
                                        {{ number_format($totalAmount, 2) }}
                                        <small class="text-muted d-block">
                                            {{ $devises->where('id', $devise_id)->first()->code ?? '' }}
                                        </small>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
        @include('livewire.articles.article-shortcut-modal')
    @endif
</div>
