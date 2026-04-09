<div wire:show="showModalDetails"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white p-4"
                 style="background:linear-gradient(135deg,#4CAF50,#2E7D32)">
                <div>
                    <h5 class="fw-bold">
                        <i class="fas fa-file-invoice me-2"></i>Détails de la commande
                    </h5>
                    <small class="opacity-75">REF: {{ $selectedCommande->reference }}</small>
                </div>
                <button class="btn-close btn-close-white"
                        wire:click="closeDetails"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                @include('print.commande-details', ['selectedCommande' => $selectedCommande])
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-4">
                <button class="btn btn-secondary me-2"
                        wire:click="closeDetails">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button class="btn btn-outline-primary"
                        onclick="printCommande()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>

        </div>
    </div>
</div>
