<div class="modal fade {{ $showModal ? 'show' : '' }}" id="deviseModal" tabindex="-1" style="display: {{ $showModal ? 'block' : 'none' }}; z-index: 1055;" aria-labelledby="deviseModalLabel"aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $articleId ? 'Modifier un article' : 'Ajouter un article' }}
                </h5>
                <button wire:click="closeModal" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Référence -->
                <div class="mb-3">
                    <label class="form-label">
                        Référence <span class="text-info">*</span>
                    </label>
                    <input type="text"
                        wire:model.defer="reference"
                        class="form-control @error('reference') is-invalid @enderror">

                    @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Catégorie -->
                <div class="mb-3">
                    <label class="form-label">
                        Catégorie <span class="text-info">*</span>
                    </label>

                    <select wire:model.defer="category_id"
                            class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">— Sélectionner une catégorie —</option>

                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Désignation -->
                <div class="mb-3">
                    <label class="form-label">
                        Désignation <span class="text-info">*</span>
                    </label>
                    <input type="text"
                        wire:model.defer="designation"
                        class="form-control @error('designation') is-invalid @enderror">

                    @error('designation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea wire:model.defer="description"
                            rows="3"
                            class="form-control @error('description') is-invalid @enderror"></textarea>

                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Devise -->
                <div class="mb-3">
                    <label class="form-label">
                        Devise <span class="text-danger">*</span>
                    </label>

                    <select wire:model.defer="devise_id"
                            class="form-select @error('devise_id') is-invalid @enderror">
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

                <!-- Prix -->
                <div class="row">

                    <!-- Prix d'achat -->
                    <div class="col-md-6 mb-3"
                        x-data="priceInput(@this, 'prix_achat')">

                        <label class="form-label">Prix d’achat</label>

                        <input
                            type="text"
                            x-model="display"
                            @input="format"
                            inputmode="decimal"
                            class="form-control @error('prix_achat') is-invalid @enderror"
                            placeholder="0.00">

                        @error('prix_achat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prix de vente -->
                    <div class="col-md-6 mb-3"
                        x-data="priceInput(@this, 'prix_vente')">

                        <label class="form-label">Prix de vente</label>

                        <input
                            type="text"
                            x-model="display"
                            @input="format"
                            inputmode="decimal"
                            class="form-control @error('prix_vente') is-invalid @enderror"
                            placeholder="0.00">

                        @error('prix_vente')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <!-- Unité -->
                <div class="mb-3">
                    <label class="form-label">Unité</label>

                    <select
                        wire:model.defer="unite"
                        class="form-select @error('unite') is-invalid @enderror">

                        <option value="">— Sélectionner une unité —</option>

                        {{-- Unités de base --}}
                        <option value="piece">Pièce</option>
                        <option value="lot">Lot</option>
                        <option value="carton">Carton</option>
                        <option value="paquet">Paquet</option>
                        <option value="boite">Boîte</option>
                    </select>

                    @error('unite')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" wire:model="status">
                    <label class="form-check-label">Article actif</label>
                </div>

            </div>

            <div class="modal-footer">
                <button wire:click="closeModal" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button wire:click="store" class="btn btn-primary">
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Backdrop -->
@if($showModal)
    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
@endif

<style>
    /* Fix pour le modal Livewire */
    .modal.show {
        display: block !important;
    }

    body.modal-open {
        overflow: hidden;
    }
</style>

@if($showModal)
    <script>
        document.body.classList.add('modal-open');
    </script>
@else
    <script>
        document.body.classList.remove('modal-open');
    </script>
@endif
