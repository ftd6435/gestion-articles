<div>
    {{-- ================= ANIMATED HEADER ================= --}}
    <div class="reception-create-header mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <div class="header-icon-box me-3">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <div>
                        <h1 class="h3 fw-bold mb-1 gradient-text">Nouvelle Réception Fournisseur</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-box-open me-2"></i>
                            Créer une réception et enregistrer les articles réceptionnés
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('stock.approvisions') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ======================================================
             LEFT COLUMN : RECEPTION INFO
        ====================================================== --}}
        <div class="col-12 col-md-3">
            <div class="info-card sticky-card">
                <div class="info-card-header">
                    <div class="header-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h5 class="mb-0">Informations Réception</h5>
                </div>

                <div class="info-card-body">
                    {{-- COMMANDE --}}
                    <div class="form-group-modern mb-4">
                        <label class="form-label-modern">
                            <i class="fas fa-file-invoice me-2"></i>
                            Commande Fournisseur <span class="text-danger">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select wire:model.live="commande_id"
                                    class="form-select-modern @error('commande_id') is-invalid @enderror">
                                <option value="">Sélectionner une commande</option>
                                @foreach($commandes as $commande)
                                    <option value="{{ $commande->id }}">
                                        {{ $commande->reference }} — {{ $commande->fournisseur->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                        @error('commande_id')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DATE --}}
                    <div class="form-group-modern mb-4">
                        <label class="form-label-modern">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Date de Réception
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-calendar input-icon-left"></i>
                            <input type="date"
                                   wire:model.defer="date_reception"
                                   class="form-control-modern">
                        </div>
                    </div>

                    {{-- MESSAGE SI AUCUNE COMMANDE --}}
                    @if(!$commande_id)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Veuillez sélectionner une commande pour continuer.
                        </div>
                    @endif

                    {{-- STATUS BADGE --}}
                    @if($selectedCommande)
                        <div class="status-card">
                            <div class="status-card-header">
                                <i class="fas fa-info-circle me-2"></i>
                                Statut de la Commande
                            </div>
                            <div class="status-card-body">
                                <div class="status-badge
                                    @if($selectedCommande->status === 'EN_COURS') status-info
                                    @elseif($selectedCommande->status === 'PARTIELLE') status-warning
                                    @else status-success
                                    @endif">
                                    <i class="fas fa-circle status-dot"></i>
                                    {{ str_replace('_',' ', $selectedCommande->status) }}
                                </div>
                            </div>
                        </div>

                        {{-- COMMANDE DETAILS --}}
                        <div class="details-box mt-3">
                            <div class="detail-item">
                                <span class="detail-label">Fournisseur</span>
                                <span class="detail-value">{{ $selectedCommande->fournisseur->name ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date commande</span>
                                <span class="detail-value">
                                    {{ \Carbon\Carbon::parse($selectedCommande->date_commande)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Articles</span>
                                <span class="detail-value">
                                    {{ count($articles) }} article(s)
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ======================================================
             RIGHT COLUMN : LIGNES RECEPTION
        ====================================================== --}}
        <div class="col-12 col-md-9">
            {{-- ================= ADD LINE FORM ================= --}}
            @if($commande_id)
                <div class="add-line-card mb-4">
                    <div class="add-line-header">
                        <div class="add-line-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h5 class="mb-0">Ajouter une Ligne de Réception</h5>
                    </div>

                    <div class="add-line-body">
                        <div class="row g-3">
                            {{-- ARTICLE --}}
                            <div class="col-md-6">
                                <label class="form-label-modern">
                                    <i class="fas fa-box me-2"></i>
                                    Article <span class="text-danger">*</span>
                                </label>
                                <div class="select-wrapper">
                                    <select wire:model.live="article_id"
                                            class="form-select-modern @error('article_id') is-invalid @enderror">
                                        <option value="">Choisir un article</option>
                                        @forelse($articles as $article)
                                            @php
                                                $cmdLine = $selectedCommande->ligneCommandes->firstWhere('article_id', $article->id);
                                                $alreadyReceived = $this->alreadyReceivedQty($article->id);
                                                $pendingInForm = collect($lines)->where('article_id', $article->id)->sum('quantity');
                                                $remaining = $cmdLine ? ($cmdLine->quantity - $alreadyReceived - $pendingInForm) : 0;
                                            @endphp
                                            <option value="{{ $article->id }}"
                                                    @if($remaining <= 0) disabled style="color: #ccc;" @endif>
                                                {{ $article->designation }}
                                                @if($remaining > 0)
                                                    - Restant: {{ $remaining }}/{{ $cmdLine->quantity }}
                                                @else
                                                    - Déjà réceptionné
                                                @endif
                                            </option>
                                        @empty
                                            <option value="" disabled>Aucun article dans cette commande</option>
                                        @endforelse
                                    </select>
                                    <i class="fas fa-chevron-down select-arrow"></i>
                                </div>
                                @error('article_id')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- MAGASIN --}}
                            <div class="col-md-3">
                                <label class="form-label-modern">
                                    <i class="fas fa-warehouse me-2"></i>
                                    Magasin <span class="text-danger">*</span>
                                </label>
                                <div class="select-wrapper">
                                    <select wire:model.live="magasin_id"
                                            class="form-select-modern @error('magasin_id') is-invalid @enderror">
                                        <option value="">Choisir</option>
                                        @foreach($magasins as $magasin)
                                            <option value="{{ (string) $magasin->id }}">
                                                {{ $magasin->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down select-arrow"></i>
                                </div>
                                @error('magasin_id')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- QUANTITE --}}
                            <div class="col-md-3">
                                <label class="form-label-modern">
                                    <i class="fas fa-hashtag me-2"></i>
                                    Quantité <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       min="1"
                                       wire:model.defer="quantity"
                                       class="form-control-modern @error('quantity') is-invalid @enderror"
                                       placeholder="0">
                                @error('quantity')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror

                                @if($article_id && $selectedCommande)
                                    @php
                                        $cmdLine = $selectedCommande->ligneCommandes->firstWhere('article_id', $article_id);
                                        $received = $cmdLine ? $this->alreadyReceivedQty($article_id) : 0;
                                        $pending  = collect($lines)->where('article_id', $article_id)->sum('quantity');
                                        $remaining = $cmdLine ? ($cmdLine->quantity - $received - $pending) : 0;
                                    @endphp

                                    <small class="text-muted d-block mt-1">
                                        Quantité restante : <strong>{{ $remaining }}</strong>
                                    </small>
                                @endif

                            </div>

                            {{-- ETAGERE --}}
                            <div class="col-md-9">
                                <label class="form-label-modern">
                                    <i class="fas fa-layer-group me-2"></i>
                                    Étagère <span class="text-danger">*</span>
                                </label>
                                <div class="select-wrapper" wire:key="etagere-select-{{ $magasin_id ?: 'none' }}">
                                    <select wire:model.live="etagere_id"
                                            class="form-select-modern @error('etagere_id') is-invalid @enderror"
                                            @disabled(count($etageres) === 0)>
                                        <option value="" @if(count($etageres) > 0) disabled @endif>
                                            {{ count($etageres) === 0 ? 'Choisir un magasin d\'abord' : 'Choisir une étagère' }}
                                        </option>
                                        @foreach($etageres as $etagere)
                                            <option value="{{ (string) $etagere->id }}">
                                                {{ $etagere->code_etagere }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down select-arrow"></i>
                                </div>
                                @error('etagere_id')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- ACTION BUTTON --}}
                            <div class="col-md-3 d-flex align-items-end">
                                <button wire:click="addLine" class="btn-add-line w-100">
                                    <i class="fas fa-plus me-2"></i>
                                    Ajouter
                                </button>
                            </div>

                            {{-- DATE EXPIRATION (optionnel) --}}
                            <div class="col-md-12 mt-2">
                                <label class="form-label-modern">
                                    <i class="fas fa-calendar-times me-2"></i>
                                    Date d'expiration (optionnel)
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-calendar input-icon-left"></i>
                                    <input type="date"
                                           wire:model.defer="date_expiration"
                                           class="form-control-modern"
                                           min="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= LINES LIST ================= --}}
                <div class="lines-card">
                    <div class="lines-card-header">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-list-ul me-3 text-primary"></i>
                            <div>
                                <h5 class="mb-0">Lignes Réceptionnées</h5>
                                <small class="text-muted">{{ count($lines) }} article(s) ajouté(s)</small>
                            </div>
                        </div>
                    </div>

                    @if(empty($lines))
                        <div class="empty-lines">
                            <div class="empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h5 class="empty-title">Aucune ligne ajoutée</h5>
                            <p class="empty-text">Utilisez le formulaire ci-dessus pour ajouter des articles</p>
                        </div>
                    @else
                        {{-- Desktop View --}}
                        <div class="lines-table d-none d-md-block">
                            @foreach($lines as $index => $line)
                                <div class="line-item" wire:key="line-{{ $index }}">
                                    <div class="line-content">
                                        <div class="line-article">
                                            <div class="article-icon">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div>
                                                <div class="article-name">{{ $line['article_name'] }}</div>
                                                <div class="article-location">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $line['magasin_name'] }} / {{ $line['etagere_name'] }}
                                                </div>
                                                @if($line['date_expiration'])
                                                    <div class="article-expiration">
                                                        <i class="fas fa-calendar-times me-1"></i>
                                                        Exp: {{ \Carbon\Carbon::parse($line['date_expiration'])->format('d/m/Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="line-quantity">
                                            <span class="quantity-badge">{{ $line['quantity'] }}</span>
                                        </div>
                                        <div class="line-actions">
                                            <button wire:click="removeLine({{ $index }})"
                                                    class="btn-remove"
                                                    title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Mobile View --}}
                        <div class="lines-mobile d-md-none">
                            @foreach($lines as $index => $line)
                                <div class="line-card-mobile" wire:key="line-mobile-{{ $index }}">
                                    <div class="line-card-mobile-header">
                                        <div class="article-icon-mobile">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="article-name-mobile">{{ $line['article_name'] }}</div>
                                            <div class="article-location-mobile">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $line['magasin_name'] }} / {{ $line['etagere_name'] }}
                                            </div>
                                            @if($line['date_expiration'])
                                                <div class="article-expiration-mobile">
                                                    <i class="fas fa-calendar-times me-1"></i>
                                                    Exp: {{ \Carbon\Carbon::parse($line['date_expiration'])->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </div>
                                        <button wire:click="removeLine({{ $index }})" class="btn-remove-mobile">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="line-card-mobile-footer">
                                        <span class="quantity-label">Quantité</span>
                                        <span class="quantity-badge-mobile">{{ $line['quantity'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Footer with Save Button --}}
                        <div class="lines-card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="total-info">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="fw-semibold">Total: {{ count($lines) }} ligne(s)</span>
                                </div>
                                <button wire:click="store" class="btn-save-reception" wire:loading.attr="disabled">
                                    <i class="fas fa-save me-2"></i>
                                    <span wire:loading.remove>Enregistrer la Réception</span>
                                    <span wire:loading>
                                        <i class="fas fa-spinner fa-spin me-2"></i>
                                        Enregistrement...
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- MESSAGE QUAND AUCUNE COMMANDE SELECTIONNEE --}}
                <div class="empty-state-card">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-list fa-3x"></i>
                    </div>
                    <h4 class="empty-state-title">Sélectionnez une commande</h4>
                    <p class="empty-state-text">
                        Veuillez sélectionner une commande dans le panneau de gauche pour commencer à ajouter des articles.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Styles pour les articles déjà réceptionnés */
    option:disabled {
        background-color: #f8f9fa;
        color: #6c757d !important;
    }

    /* Empty state */
    .empty-state-card {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .empty-state-icon {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }

    .empty-state-title {
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        color: #6c757d;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Articles expirations */
    .article-expiration {
        font-size: 0.8rem;
        color: #dc3545;
        margin-top: 0.25rem;
    }

    .article-expiration-mobile {
        font-size: 0.75rem;
        color: #dc3545;
        margin-top: 0.25rem;
    }

    /* Loading state */
    .btn-save-reception:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>
@endpush
