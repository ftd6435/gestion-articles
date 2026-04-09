{{-- ========== MODAL: RECEPTION DETAILS ========== --}}
@if($showDetailsModal && $selectedReception)

<div wire:show="showDetailsModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable a4-modal">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white p-4"
                style="background: linear-gradient(135deg,#4e54c8,#8f94fb)">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-truck-loading me-2"></i>Détails de la réception
                    </h5>
                    <small class="opacity-75">
                        REF: {{ $selectedReception->reference }}
                    </small>
                </div>
                <button class="btn-close btn-close-white"
                    wire:click="closeDetailsModal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-white">
                @include('print.reception-details', ['selectedReception' => $selectedReception])
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light p-3">
                <button class="btn btn-secondary me-2"
                    wire:click="closeDetailsModal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button class="btn btn-primary"
                    onclick="printReceptionDetails()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>

        </div>
    </div>
</div>

@endif
