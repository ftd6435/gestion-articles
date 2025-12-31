<div class="modal fade {{ $showModal ? 'show' : '' }}" id="categoryModal" tabindex="-1" style="display: {{ $showModal ? 'block' : 'none' }}; z-index: 1055;" aria-labelledby="categoryModalLabel"aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    {{ $categoryId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                </h5>
                <button wire:click="closeModal" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="name"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Nom catégorie">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea wire:model.defer="description" class="form-control" placeholder="Description de la catégorie d'article..."></textarea>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" wire:model="status">
                    <label class="form-check-label">Catégorie active</label>
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
