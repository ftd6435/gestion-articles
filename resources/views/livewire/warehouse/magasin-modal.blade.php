<div class="modal fade {{ $showModal ? 'show' : '' }}" id="magasinModal" tabindex="-1" style="display: {{ $showModal ? 'block' : 'none' }}; z-index: 1055;" aria-labelledby="deviseModalLabel"aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    {{ $magasinId ? 'Modifier le magasin' : 'Nouveau magasin' }}
                </h5>
                <button wire:click="closeModal" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Code Magasin <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="code_magasin"
                           class="form-control @error('code_magasin') is-invalid @enderror" placeholder="Ex: MA0001">
                    @error('code_magasin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nom Magasin <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="nom"
                           class="form-control @error('nom') is-invalid @enderror" placeholder="Nom du magasin">
                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Localisation</label>
                    <input type="text" wire:model.defer="localisation" class="form-control" placeholder="Ex: Cosa">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" wire:model="status">
                    <label class="form-check-label">Magasin actif</label>
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
