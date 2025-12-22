<div class="modal fade {{ $showModal ? 'show' : '' }}" id="deviseModal" tabindex="-1" style="display: {{ $showModal ? 'block' : 'none' }}; z-index: 1055;" aria-labelledby="deviseModalLabel"aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $deviseId ? 'Modifier la devise' : 'Nouvelle devise' }}
                </h5>
                <button wire:click="closeModal" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Code <span class="text-info">*</span></label>
                    <input type="text" wire:model.defer="code"
                           class="form-control @error('code') is-invalid @enderror">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nom <span class="text-info">*</span></label>
                    <input type="text" wire:model.defer="libelle"
                           class="form-control @error('libelle') is-invalid @enderror">
                    @error('libelle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Symbole</label>
                    <input type="text" wire:model.defer="symbole"
                           class="form-control @error('symbole') is-invalid @enderror">
                    @error('symbole') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" wire:model="status">
                    <label class="form-check-label">Devise active</label>
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
