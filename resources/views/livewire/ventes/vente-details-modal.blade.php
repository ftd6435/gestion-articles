<div wire:show="showDetailsModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white p-4"
                 style="background:linear-gradient(135deg,#667eea,#764ba2)">
                <div>
                    <h5 class="fw-bold">
                        <i class="fas fa-receipt me-2"></i>Détails de la vente
                    </h5>
                    <small class="opacity-75">REF: {{ $selectedVente->reference }}</small>
                </div>
                <button class="btn-close btn-close-white"
                        wire:click="closeDetailsModal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                @include('print.vente-details', ['selectedVente' => $selectedVente])
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-4">
                <button class="btn btn-secondary me-2"
                        wire:click="closeDetailsModal">
                    Fermer
                </button>
                <button class="btn btn-primary"
                        onclick="printModal()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>

        </div>
    </div>
</div>
