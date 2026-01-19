<div wire:show="showModal"
     x-transition.opacity.duration.200ms
     x-transition.scale.duration.200ms
     class="modal-backdrop-custom">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white px-4 py-3">
                <h5 class="modal-title">
                    <i class="fa fa-plus-circle me-2"></i> Nouvel Article Rapide
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
            </div>
            <form wire:submit.prevent="storeArticle"> <!-- FORM STARTS HERE -->
                <div class="modal-body bg-white px-4 py-3">
                    <div class="row">
                        <!-- Référence -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Référence <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('newArticle.reference') is-invalid @enderror"
                                   wire:model="newArticle.reference"
                                   placeholder="Ex: ART-0001">
                            @error('newArticle.reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Catégorie -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select @error('newArticle.category_id') is-invalid @enderror"
                                    wire:model="newArticle.category_id">
                                <option value="">— Sélectionner —</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('newArticle.category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Désignation -->
                    <div class="mb-3">
                        <label class="form-label">Désignation <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('newArticle.designation') is-invalid @enderror"
                               wire:model="newArticle.designation"
                               placeholder="Nom de l'article">
                        @error('newArticle.designation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control"
                                  wire:model="newArticle.description"
                                  rows="2"
                                  placeholder="Description optionnelle"></textarea>
                    </div>

                    <div class="row">
                        <!-- Devise -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Devise <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('devise_id') is-invalid @enderror"
                                    wire:model="devise_id">
                                <option value="">— Sélectionner une devise —</option>
                                @foreach($devises as $devise)
                                    <option value="{{ $devise->id }}">
                                        {{ $devise->code }} ({{ $devise->symbole }})
                                    </option>
                                @endforeach
                            </select>
                            @error('devise_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unité -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unité</label>
                            <select class="form-select @error('newArticle.unite') is-invalid @enderror"
                                    wire:model="newArticle.unite">
                                <option value="">— Sélectionner —</option>
                                <option value="piece">Pièce</option>
                                <option value="lot">Lot</option>
                                <option value="carton">Carton</option>
                                <option value="paquet">Paquet</option>
                                <option value="boite">Boîte</option>
                            </select>
                            @error('newArticle.unite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Prix Section - SIMPLIFIED WITHOUT ALPINE -->
                    <div class="row">
                        <!-- Prix d'achat -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Prix d'achat <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('newArticle.prix_achat') is-invalid @enderror"
                                   wire:model="newArticle.prix_achat"
                                   placeholder="0.00">
                            @error('newArticle.prix_achat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Prix de vente -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Prix de vente <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('newArticle.prix_vente') is-invalid @enderror"
                                   wire:model="newArticle.prix_vente"
                                   placeholder="0.00">
                            @error('newArticle.prix_vente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" wire:model="newArticle.status">
                        <label class="form-check-label">Article actif</label>
                    </div>
                </div>
                <div class="modal-footer bg-white px-4 py-3">
                    <button type="button" class="btn btn-secondary me-2" wire:click="closeModal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-2"></i> Créer et sélectionner
                    </button>
                </div>
            </form> <!-- FORM ENDS HERE -->
        </div>
    </div>
</div>
