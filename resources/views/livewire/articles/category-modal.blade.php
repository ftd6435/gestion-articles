<div wire:show="showCategoryModal"
            x-transition.opacity.duration.200ms
            x-transition.scale.duration.200ms
            class="modal-backdrop-custom"
        >
    <div class="modal-dialog modal-dialog-centered bg-white">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white px-4 py-3">
                <h5 class="modal-title">
                    {{ $categoryId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                </h5>
                <button wire:click="closeCategoryModal" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <div class="mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="name"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Nom catégorie">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea wire:model.defer="description" class="form-control" placeholder="Description de la catégorie d'article..."></textarea>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" wire:model="status">
                    <label class="form-check-label">Catégorie active</label>
                </div>
            </div>

            <div class="modal-footer px-4 py-3">
                <button wire:click="closeCategoryModal" class="btn btn-light me-2" data-bs-dismiss="modal">Annuler</button>
                <button wire:click="storeCategory" class="btn btn-primary">
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>
