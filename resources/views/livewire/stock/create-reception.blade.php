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
                    Retour à la liste
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
                                        {{ $commande->reference }} — {{ $commande->fournisseur->name }}
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
                                <span class="detail-value">{{ $selectedCommande->fournisseur->name }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date commande</span>
                                <span class="detail-value">
                                    {{ \Carbon\Carbon::parse($selectedCommande->date_commande)->format('d/m/Y') }}
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
                                <select wire:model.defer="article_id"
                                        class="form-select-modern @error('article_id') is-invalid @enderror">
                                    <option value="">Choisir un article</option>
                                    @foreach($articles as $article)
                                        <option value="{{ $article->id }}">
                                            {{ $article->designation }}
                                        </option>
                                    @endforeach
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
                                        <option value="{{ $magasin->id }}">
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

                                <small class="text-muted">
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
                            <div class="select-wrapper">
                                <select wire:model.defer="etagere_id"
                                        class="form-select-modern @error('etagere_id') is-invalid @enderror"
                                        @disabled(empty($etageres))>
                                    <option value="">{{ empty($etageres) ? 'Choisir un magasin d\'abord' : 'Choisir une étagère' }}</option>
                                    @foreach($etageres as $etagere)
                                        <option value="{{ $etagere->id }}">
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
                            <button wire:click="store" class="btn-save-reception">
                                <i class="fas fa-save me-2"></i>
                                Enregistrer la Réception
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
