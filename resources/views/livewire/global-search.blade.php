<div class="global-search-container position-relative" style="z-index: 9999;">
    <!-- Barre de recherche -->
    <div class="input-group search-input-group bg-white rounded">
        <span class="input-group-text bg-white border-end-0 search-icon">
            <i class="fas fa-search text-muted"></i>
        </span>
        <input type="text"
               class="form-control bg-white border-start-0 search-input shadow-none"
               placeholder="Rechercher un menu, une page..."
               wire:model.live="search"
               wire:keydown.escape="closeResults"
               wire:keydown.arrow-up="$dispatch('arrow-up')"
               wire:keydown.arrow-down="$dispatch('arrow-down')"
               wire:keydown.enter="$dispatch('search-enter')"
               aria-label="Recherche globale">

        @if($search)
            <button class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y me-3 clear-search"
                    wire:click="closeResults"
                    type="button"
                    style="z-index: 5;">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>

    <!-- Résultats de recherche -->
    @if($isOpen && !empty($results))
        <div class="search-results-dropdown shadow-lg border rounded-3 mt-1 position-absolute start-0 end-0"
             wire:click.outside="closeResults"
             style="z-index: 99999; margin-top: 2px;">
            <div class="search-results-header p-3 border-bottom bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-search me-2"></i>
                        Résultats de recherche ({{ count($results) }})
                    </h6>
                    <small class="text-muted">{{ strlen($search) }} caractères</small>
                </div>
            </div>

            <div class="search-results-body bg-white" style="max-height: 400px; overflow-y: auto;">
                @php
                    $groupedResults = collect($results)->groupBy('category');
                @endphp

                @foreach($groupedResults as $category => $items)
                    <div class="search-category-section">
                        <div class="search-category-header px-3 py-2 bg-light">
                            <small class="text-uppercase fw-bold text-muted">
                                <i class="fas fa-folder me-1"></i>
                                {{ $category }}
                            </small>
                        </div>

                        @foreach($items as $index => $item)
                            <a href="{{ $item['url'] }}"
                               class="search-result-item d-block px-3 py-3 text-decoration-none border-bottom bg-white"
                               wire:click="navigateTo('{{ $item['url'] }}')"
                               wire:key="result-{{ $index }}"
                               data-category="{{ $item['category'] }}">
                                <div class="d-flex align-items-center">
                                    <div class="search-result-icon me-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                            <i class="{{ $item['icon'] }} text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="search-result-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="fw-semibold mb-1 text-dark">{{ $item['name'] }}</h6>
                                            <small class="text-muted ms-2">{{ $item['category'] }}</small>
                                        </div>
                                        <p class="text-muted small mb-0">{{ $item['description'] }}</p>
                                        <small class="text-primary">
                                            <i class="fas fa-link me-1"></i>
                                            {{ $item['url'] }}
                                        </small>
                                    </div>
                                    <div class="search-result-arrow ms-3">
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="search-results-footer p-3 border-top bg-light">
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">
                            <i class="fas fa-keyboard me-1"></i>
                            Utilisez ↑↓ pour naviguer
                        </small>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted">
                            <i class="fas fa-enter me-1"></i>
                            Entrée pour sélectionner
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Message aucun résultat -->
    @if($isOpen && empty($results) && strlen($search) >= 2)
        <div class="search-results-dropdown shadow-lg border rounded-3 mt-1 position-absolute start-0 end-0"
             wire:click.outside="closeResults"
             style="z-index: 99999; margin-top: 2px;">
            <div class="p-4 text-center bg-white">
                <div class="mb-3">
                    <i class="fas fa-search fa-2x text-muted mb-3"></i>
                    <h6 class="fw-semibold mb-2">Aucun résultat trouvé</h6>
                    <p class="text-muted small mb-0">
                        Aucun menu ne correspond à "{{ $search }}"
                    </p>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        Essayez d'autres termes comme : ventes, stock, clients...
                    </small>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Navigation au clavier améliorée
        let currentIndex = -1;
        const resultItems = [];

        // Écouter les événements de flèches
        Livewire.on('arrow-up', () => {
            if (resultItems.length > 0) {
                currentIndex = Math.max(-1, currentIndex - 1);
                updateSelection();
            }
        });

        Livewire.on('arrow-down', () => {
            if (resultItems.length > 0) {
                currentIndex = Math.min(resultItems.length - 1, currentIndex + 1);
                updateSelection();
            }
        });

        Livewire.on('search-enter', () => {
            if (currentIndex >= 0 && resultItems[currentIndex]) {
                resultItems[currentIndex].click();
            }
        });

        // Mettre à jour les éléments de résultats
        Livewire.hook('morph.updated', ({ component }) => {
            if (component.name === 'global-search') {
                setTimeout(() => {
                    resultItems.length = 0;
                    document.querySelectorAll('.search-result-item').forEach(item => {
                        resultItems.push(item);
                    });
                    currentIndex = -1;
                    updateSelection();
                }, 10);
            }
        });

        function updateSelection() {
            resultItems.forEach((item, index) => {
                if (index === currentIndex) {
                    item.classList.add('selected');
                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.classList.remove('selected');
                }
            });
        }

        // Focus sur la recherche quand on appuie sur Ctrl+K
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('.search-input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }

            // Échap pour fermer
            if (e.key === 'Escape') {
                @this.closeResults();
            }
        });

        // Empêcher le comportement par défaut des flèches dans l'input
        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('search-input') &&
                (e.key === 'ArrowUp' || e.key === 'ArrowDown')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .global-search-container {
        position: relative;
        width: 100%;
        max-width: 300px;
    }

    .search-input-group {
        position: relative;
        border: 1px solid #dee2e6 !important;
        transition: all 0.2s ease;
    }

    .search-input-group:focus-within {
        border-color: #8f94fb !important;
        box-shadow: 0 0 0 0.2rem rgba(142, 148, 251, 0.25) !important;
        z-index: 10000;
    }

    .search-input {
        padding-right: 40px !important;
        transition: all 0.2s ease;
        border: none !important;
        box-shadow: none !important;
    }

    .search-input:focus {
        border: none !important;
        box-shadow: none !important;
        background-color: white !important;
    }

    .search-icon {
        background-color: white !important;
        border: none !important;
        transition: all 0.2s ease;
    }

    .search-input-group:focus-within .search-icon {
        border-color: #8f94fb !important;
    }

    .clear-search {
        opacity: 0.6;
        transition: opacity 0.2s ease;
        z-index: 10001;
    }

    .clear-search:hover {
        opacity: 1;
        color: #dc3545 !important;
    }

    .search-results-dropdown {
        background: white;
        animation: slideDown 0.2s ease-out;
        max-height: 500px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1), 0 5px 10px rgba(0,0,0,0.05);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .search-result-item {
        transition: all 0.2s ease;
        color: inherit;
        background-color: white;
    }

    .search-result-item:hover {
        background-color: rgba(78, 84, 200, 0.05) !important;
        transform: translateX(2px);
    }

    .search-result-item.selected {
        background-color: rgba(78, 84, 200, 0.1) !important;
        border-left: 3px solid #4e54c8 !important;
    }

    .search-result-icon {
        width: 40px;
        flex-shrink: 0;
    }

    .search-category-section:last-child .search-result-item:last-child {
        border-bottom: none !important;
    }

    .search-results-body {
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
    }

    .search-results-body::-webkit-scrollbar {
        width: 6px;
    }

    .search-results-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .search-results-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .search-results-body::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .global-search-container {
            max-width: 100%;
            margin: 0;
        }

        .search-results-dropdown {
            position: fixed !important;
            top: 70px !important;
            left: 15px !important;
            right: 15px !important;
            max-height: calc(100vh - 100px);
            margin-top: 5px !important;
        }
    }

    @media (max-width: 576px) {
        .search-results-dropdown {
            top: 65px !important;
            left: 10px !important;
            right: 10px !important;
        }

        .search-input::placeholder {
            font-size: 0.9rem;
        }
    }
</style>
@endpush
